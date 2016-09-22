<?php

namespace App\Http\Controllers;

use App\Models\ReactionAlias;
use App\Models\Team;
use App\Models\User;
use App\Models\Reaction;
use App\Models\Post;
use App\Models\PostUserReaction;
use App\Collections\PostUserReaction as PostUserReactionCollection;
use App\Collections\Reaction as ReactionCollection;
use App;
use DB;

class UserController extends Controller
{
	public function showLeaderboardAction($team_domain, $user_handle)
	{
		$team = Team::where(['domain' => $team_domain])->first();

		if (!$team) {
			App::abort(404);
		}

		$users = User::where(['team_id' => $team->team_id/*, 'handle' => $user_handle*/])->get();

		PostUserReactionCollection::getTotalReactionCountsByEachUserOnTeamAndAddToUsers($team, $users);
//		PostUserReactionCollection::getAllPostUserReactionsByEachUserOnTeamAndAddToUsers($team, $users);

		$user  = $users->where('handle', $user_handle)->first();

		if (!$user) {
			App::abort(404);
		}

		$emojis    									   = ReactionCollection::getReactionsAndReactionAliasesByTeam($team, true);
		$reactions_to_this_users_posts_grouped_by_user = PostUserReactionCollection::getTotalReactionsToASingleUsersPostsGroupedByAllUsersAndAddToUsers($user, $users);


		$this->_layout->team = $team;
		$this->_layout->content = view('user.leaderboard', [
			'team'   => $team,
			'users'  => $users,
			'user'   => $user,
			'emojis' => $emojis,
			'reactions_to_this_users_posts_grouped_by_user' => $reactions_to_this_users_posts_grouped_by_user
		]);

		return $this->_layout;
	}
}