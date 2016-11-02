<?php

namespace App\Console\Commands;

use App\Models\ReactionAlias;
use Frlnc\Slack\Http\SlackResponseFactory;
use Frlnc\Slack\Http\CurlInteractor;
use Frlnc\Slack\Core\Commander;
use App\Models\Team;
use App\Models\User;
use App\Models\Reaction;
use App\Models\Post;
use App\Models\PostUserReaction;
use App\Models\PostMessage;
use App\Models\Token;
use App\Models\ReactionFetchLog;
use App\Collections\User as UserCollection;
use DB;
use App;
use Exception;

class SlackDataFetch extends CommandAbstract
{
    const FORBIDDEN_RESPONSE_RECEIVED = 'FORBIDDEN_RESPONSE_RECEIVED';
    const OLD_POST_THRESHOLD_SECONDS = 60 * 60 * 24 * 14; // 2 weeks

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'slackleaderboard:fetchReactions {team_id?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch data from Slack';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
	{
        /**
         * @var $reaction_fetch_log ReactionFetchLog
         */
		ini_set('max_execution_time', 0);
		ini_set('xdebug.max_nesting_level', 99999999);

        $team_id = $this->argument('team_id');

        if ($team_id) {
            $reaction_fetch_logs = ReactionFetchLog::where(['team_id' => $team_id])->get();

            if (!$reaction_fetch_logs->first()) {
                $reaction_fetch_log = new ReactionFetchLog();
                $reaction_fetch_log->team_id = $team_id;
                $reaction_fetch_logs->push($reaction_fetch_log);
            }
        } else { // no team id argument passed;
            $reaction_fetch_logs = ReactionFetchLog::where(['in_process' => false])->get();
        }

        foreach ($reaction_fetch_logs as $reaction_fetch_log) {
            // fetch fresh copy of reaction fetch log from DB
            $reaction_fetch_log_in_db = ReactionFetchLog::where(['team_id' => $reaction_fetch_log->team_id])->first();

            if ($reaction_fetch_log_in_db) { // if there isn't a corresponding reaction fetch log in the DB it means that we have a new row to insert on our hands (brand new team and scrapes haven't started happening yet)
                $reaction_fetch_log = $reaction_fetch_log_in_db;
            }

            if ($reaction_fetch_log->hasMostLikelyBeenStalled()) {
                $reaction_fetch_log->in_process = false;
            }

            if ($reaction_fetch_log->in_process) {
                continue;
            }

            $reaction_fetch_log->in_process = true;
            $reaction_fetch_log->save();

            try {
                $slack_tokens_for_this_team = DB::table('token AS t')
                    ->join('user AS u', 't.user_id', '=', 'u.user_id')
                    ->where('u.team_id', '=', $reaction_fetch_log->team_id)
                    ->select('t.token')
                    ->get()
                ;

                foreach ($slack_tokens_for_this_team as $slack_token) { // go through each slack token for the team, and the first one to get through the entire loop will win! (we'll break the loop after)
                    try {
                        $interactor     = new CurlInteractor();
                        $interactor->setResponseFactory(new SlackResponseFactory());
                        $commander      = new Commander($slack_token->token, $interactor);

                //		DB::beginTransaction();

                        // team
                        $response = $commander->execute('team.info');
                        if ($this->_isForbiddenResponse($response)) {
                            throw new Exception(self::FORBIDDEN_RESPONSE_RECEIVED);
                        }

                        $team = Team::importFromSlackResponseBody($response->getBody());
                        $team->save();

                        // users
                        $response  	    = $commander->execute('users.list');
                        if ($this->_isForbiddenResponse($response)) {
                            throw new Exception(self::FORBIDDEN_RESPONSE_RECEIVED);
                        }

                        UserCollection::importFromSlackResponseBody($response->getBody());

                        // emoji.list
                        $response  	    = $commander->execute('emoji.list');
                        if ($this->_isForbiddenResponse($response)) {
                            throw new Exception(self::FORBIDDEN_RESPONSE_RECEIVED);
                        }

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
                            try {
                                $this->_savePostsAndPostUserReactions($commander, $team, $users, $user->slack_user_id);
                            } catch (Exception $e) {
                                if ($e->getMessage() === self::FORBIDDEN_RESPONSE_RECEIVED) {  // expired token; delete
                                    throw $e;
                                } else {
                                    continue;
                                }
                            }
                        }

                        $team->posts_from_beginning_of_time_fetched = false;
                        $team->save();

                        $reaction_fetch_log->in_process = false;
                        $reaction_fetch_log->save();
                        //		DB::commit();
                        break; // hooray, made it to the end for this team, don't need to use any of the other tokens!
                    } catch (Exception $e) {
                        if ($e->getMessage() === self::FORBIDDEN_RESPONSE_RECEIVED) {  // expired token; delete
                            DB::table('token')->where('token', '=', $slack_token)->delete();
                        }
                    }
                }
            } catch (Exception $e) {
                $reaction_fetch_log->in_process = false;
                $reaction_fetch_log->save();
            }
        }
	}

	private function _savePostsAndPostUserReactions(Commander $commander, $team, $users, $slack_user_id, $page_num = 1)
	{
		static $post_primary_keys_saved_in_this_run = [];
		static $posts_by_team_id = [];
		static $reactions_by_team_id = [];

		if (!(isset($posts_by_team_id[$team->getKey()], $reactions_by_team_id[$team->getKey()]))) {
			$posts_by_team_id[$team->getKey()]      = Post::where(['team_id' => $team->getKey()])->get();
			$reactions_by_team_id[$team->getKey()]  = Reaction::with('aliases')->where(['team_id' => $team->getKey()])->orWhere(['team_id' => null])->get();
		}

		$posts     = $posts_by_team_id[$team->getKey()];
        $reactions = $reactions_by_team_id[$team->getKey()];

		$response  	    = $commander->execute('reactions.list', [
			'full'  => true,
			'page'  => $page_num,
			'count' => 100,
			'user'  => $slack_user_id
		]);

        if ($this->_isForbiddenResponse($response)) {
            throw new Exception(self::FORBIDDEN_RESPONSE_RECEIVED);
        }

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

			// comb through only last 2 weeks' posts after we fetch the whole history on the initial scrape
            $is_too_old_to_include = $team->posts_from_beginning_of_time_fetched && $timestamp + self::OLD_POST_THRESHOLD_SECONDS < time();
            if ($is_too_old_to_include) {
                break;
            }

			$post_primary_keys_saved_in_this_run[] = $slack_post_primary_key;

			$post = $posts->where('slack_post_id', $slack_post_primary_key)->first() ?: new Post();

			// if too fresh, continue
			if (strtotime($post->updated_at) > strtotime('now - 60 min')) {
				continue;
			}

			$user = $users->where('slack_user_id', $meta['user'])->first();

			if (!$user) {
				throw new Exception('User not found for post');
			}

			$post->slack_post_id    = $slack_post_primary_key;
			$post->team_id          = $team->getKey();
			$post->user_id          = $user->user_id;
			$post->slack_created_at = date('Y-m-d H:i:s', $timestamp);
			$post->url              = $url;
			$post->save();

            if ($item['type'] === 'message') {
                $post_message = PostMessage::find($post->getKey()) ?: new PostMessage();
                $post_message->post_id = $post->getKey();
                $post_message->message = substr($item['message']['text'], 0, 1000);
                $post_message->message_truncated = strlen($item['message']['text']) > 1000;

                $post_message->save();
            }

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

	private function _isForbiddenResponse($response)
    {
        return (4 == substr($response->getStatusCode(), 0, 1));
    }
}
