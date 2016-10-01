<?php

namespace App\Http\Controllers;

use App\Models\ReactionAlias;
use Frlnc\Slack\Http\SlackResponseFactory;
use Frlnc\Slack\Http\CurlInteractor;
use Frlnc\Slack\Core\Commander;
use App\Models\Team;
use App\Models\User;
use App\Models\Reaction;
use App\Models\Post;
use App\Models\PostUserReaction;
use DB;

class SlackController extends Controller
{
	public function guestHomepageAction()
	{
		$current_user = session()->get('user');

		if ($current_user) {
			$team = Team::find($current_user->team_id)->first();
			if ($team) {
				return redirect()->action(
					'TeamController@showLeaderboardAction', ['domain' => $team->domain]
				);
			}
		}

		$sign_in_url = config('app.slack_oauth_url');

		$this->_layout->content = view('guest.homepage', [
			'sign_in_url' => $sign_in_url
		]);

		return $this->_layout;
	}

	public function fetchData()
	{
		ini_set('max_execution_time', 0);
		ini_set('xdebug.max_nesting_level', 99999999);

		$slack_token = config('app.slack_access_token');

		$interactor     = new CurlInteractor;
        $interactor->setResponseFactory(new SlackResponseFactory());
        $commander      = new Commander($slack_token, $interactor);

//		DB::beginTransaction();

		// team
		$response  	   = $commander->execute('team.info');
		$team_response = $response->getBody()['team'];
		$team          = Team::where(['slack_team_id' => $team_response['id']])->first() ?: new Team();

		$team->slack_team_id = $team_response['id'];
		$team->name          = $team_response['name'];
		$team->domain        = $team_response['domain'];
		$team->email_domain  = $team_response['email_domain'];
		$team->icon          = $team_response['icon']['image_original'];

		$team->save();

		// users
		$response  	    = $commander->execute('users.list');
		$users_response = $response->getBody()['members'];

		$users          = User::where(['team_id' => $team->getKey()])->get();

		foreach ($users_response as $member) {
			$user 			 		= $users->where('slack_user_id', $member['id'])->first() ?: new User();
			$user['slack_user_id']  = $member['id'];
			$user['team_id'] 		= $team->getKey();
			$user['name_binary']  	= isset($member['real_name']) ? $member['real_name'] : $member['profile']['real_name'];
			$user['name']    		= remove_emojis($user['name_binary']);
			$user['handle']  		= $member['name'];
			$user['avatar']			= isset($member['profile']['image_original']) ? $member['profile']['image_original'] : $member['profile']['image_192'];
			$user['email'] 			= isset($member['profile']['email']) ? $member['profile']['email'] : null;
			$user['slack_deleted']  = $member['deleted'];

			if ($member['deleted']) {
				$user['slack_restricted'] = $user['slack_ultra_restricted'] = true;
			} else {
				$user['slack_admin'] 		 	= $member['is_admin'];
				$user['slack_owner']		 	= $member['is_owner'];
				$user['slack_primary_owner'] 	= $member['is_primary_owner'];
				$user['slack_restricted']    	= $member['is_restricted'];
				$user['slack_ultra_restricted'] = $member['is_ultra_restricted'];
				$user['slack_bot']			 	= $member['is_bot'];
			}

			$user->save();
		}

		// emoji.list
		$response  	    = $commander->execute('emoji.list');
		$emoji_response = $response->getBody()['emoji'];

		$emojis         					  = Reaction::with('aliases')->where('team_id', $team->getKey())->get();
		$emoji_aliases_by_foreign_reference   = [];
		$emoji_reaction_ids_by_main_alias     = [];

		foreach ($emoji_response as $alias => $image_url) {
			preg_match('#alias:(.+)#', $image_url, $matches);
			$is_foreign_reference = isset($matches[1]);
			if ($is_foreign_reference) {
				$foreign_reference_name = $alias;
				$alias 					= $matches[1];
				$emoji_aliases_by_foreign_reference[$foreign_reference_name] = $alias;
				continue;
			}

			$emoji 			  = $emojis->whereInRelationship('aliases', 'alias', $alias)->first() ?: new Reaction();
			$emoji->team_id   = $team->getKey();
			$emoji->is_custom = true;
			$emoji->image 	  = $image_url;
			$emoji->save();

			$emoji_reaction_ids_by_main_alias[$alias] = $emoji->getKey();

			$emoji_alias = ReactionAlias::where(['reaction_id' => $emoji->getKey(), 'alias' => $alias])->first() ?: new ReactionAlias();

			$emoji_alias->reaction_id = $emoji->getKey();
			$emoji_alias->alias = $alias;
			$emoji_alias->is_main_alias = true;
			$emoji_alias->save();
		}

		// get default emoji set (team id null) and merge with $emoji_reaction_ids_by_main_alias
		$default_emoji_reaction_set = Reaction::with('aliases')->where(['team_id' => null])->get();
		foreach ($default_emoji_reaction_set as $reaction) {
			foreach ($reaction->aliases as $alias) {
				if (!$alias->is_main_alias) {
					continue;
				}

				$emoji_reaction_ids_by_main_alias[$alias->alias] = $alias->reaction_id;
			}
		}

		foreach ($emoji_reaction_ids_by_main_alias as $main_alias => $reaction_id) {
			foreach ($emoji_aliases_by_foreign_reference as $foreign_reference_name => $alias) {
				if ($alias === $main_alias) {
					$emoji_alias = ReactionAlias::where(['reaction_id' => $reaction_id, 'alias' => $foreign_reference_name])->first() ?: new ReactionAlias();
					$emoji_alias->reaction_id = $reaction_id;
					$emoji_alias->alias = $foreign_reference_name;
					$emoji_alias->is_main_alias = false;
					$emoji_alias->save();
				}
			}
		}

		// reactions.list
		$users = User::where(['team_id' => $team->getKey()])->get();

		foreach ($users as $user) {
			// TODO - modify logic to comb only through last 2 weeks posts after we fetch the whole history on the initial scrape
			$this->_savePostsAndPostUserReactions($commander, $team, $users, $user->slack_user_id);
		}

//		DB::commit();
	}

	private function _savePostsAndPostUserReactions(Commander $commander, $team, $users, $slack_user_id, $page_num = 1)
	{
		static $post_primary_keys_saved_in_this_run = [];
		static $posts;
		static $reactions;

		if (!(isset($posts, $reactions))) {
			$posts		    = Post::where(['team_id' => $team->getKey()])->get();
			$reactions      = Reaction::with('aliases')->where(['team_id' => $team->getKey()])->orWhere(['team_id' => null])->get();
		}

		$response  	    = $commander->execute('reactions.list', [
			'full'  => true,
			'page'  => $page_num,
			'count' => 100,
			'user'  => $slack_user_id
		]);

		$paging         = $response->getBody()['paging'];
		$items          = $response->getBody()['items'];

		foreach ($items as $item) {
			switch ($item['type']) {
				case 'message':
					if (isset($item['message']['bot_id'])) {
						continue;
					}

					$slack_post_primary_key = $item['message']['ts'] . $item['channel'];
					$meta        = $item['message'];

					$timestamp   = intval($meta['ts']);

					if (!isset($meta['permalink'])) {
						continue;
					}

					$url         = $meta['permalink'];
					break;
				case 'file':
					$slack_post_primary_key = $item['file']['id'];
					$meta        			= $item['file'];

					if (!isset($meta['permalink'])) {
						continue;
					}

					$timestamp   			= $meta['timestamp'];
					$url         			= $meta['permalink'];
					break;
				case 'file_comment':
					if (!isset($item['file']['permalink'])) {
						continue;
					}

					$slack_post_primary_key = $item['file']['id'] . '-' . $item['comment']['id'];
					$url         			= $item['file']['permalink'];

					$meta 					= $item['comment'];
					$timestamp 				= $meta['timestamp'];

					break;
				default:
					throw new \Exception("not a message or file, but a {$item['type']}");
					continue; // TODO - reconcile exception
					break;
			}

			// for some bizarre reason $slack_post_primary_key isn't being set sometimes from the switch above.... just continue here
			if (!isset($slack_post_primary_key)) {
				continue;
			}

			if (in_array($slack_post_primary_key, $post_primary_keys_saved_in_this_run)) {
				continue;
			}

			if (!isset($meta['user'])) {
				continue;
			}

			if (!isset($url)) {
				continue;
			}

			if (!isset($timestamp)) {
				continue;
			}

			$post_primary_keys_saved_in_this_run[] = $slack_post_primary_key;

			$post = $posts->where('slack_post_id', $slack_post_primary_key)->first() ?: new Post();

			// if too fresh, continue
			if (strtotime($post->updated_at) > strtotime('now - 60 min')) {
				continue;
			}

			$user = $users->where('slack_user_id', $meta['user'])->first();

			if (!$user) {
				throw new \Exception('User not found for post');
			}

			$post->slack_post_id    = $slack_post_primary_key;
			$post->team_id          = $team->getKey();
			$post->user_id          = $user->user_id;
			$post->slack_created_at = date('Y-m-d H:i:s', $timestamp);
			$post->url              = $url;

			$post->save();

			// delete post user reactions (users can undo their reactions, etc. start fresh)
			PostUserReaction::where('post_id', $post->getKey())->delete();

			// save reactions for post
			foreach ($meta['reactions'] as $reaction_in_slack) {
				$alias_sans_colons = explode(':', $reaction_in_slack['name'])[0];
				preg_match('#::skin\-tone\-(\d+)#', $reaction_in_slack['name'], $matches);
				$skin_tone   = isset($matches[1]) ? $matches[1] : 1;
				$reaction    = $reactions->whereInRelationship('aliases', 'alias', $alias_sans_colons, true, true)->first();

				if (!$reaction) { // reaction has since been deleted
					continue;
				}

				$reaction_id = $reaction->getKey();

				foreach ($reaction_in_slack['users'] as $order => $reaction_in_slack_user) {
					$post_user_reaction 			 = new PostUserReaction();
					$post_user_reaction->post_id     = $post->getKey();
					$post_user_reaction->user_id     = $users->where('slack_user_id', $reaction_in_slack_user)->first()->getKey();
					$post_user_reaction->reaction_id = $reaction_id;
					$post_user_reaction->skin_tone   = $skin_tone;
					$post_user_reaction->order       = $order + 1; // let's make this one-based for sanity's sake

					$post_user_reaction->save();
				}
			}
		}

		if ($page_num < $paging['pages']) {
			$this->_savePostsAndPostUserReactions($commander, $team, $users, $slack_user_id, $page_num + 1);
		}
	}
}