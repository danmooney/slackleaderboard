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

class TeamController extends Controller
{
	public function showLeaderboardAction($team_domain)
	{
		$team = Team::where(['domain' => $team_domain])->first();

		if (!$team) {
			App::abort(404);
		}

		$users = User::where(['team_id' => $team->team_id])->get();

		PostUserReactionCollection::getTotalReactionCountsByEachUserOnTeamAndAddToUsers($team, $users);
		PostUserReactionCollection::getAllPostUserReactionsByEachUserOnTeamAndAddToUsers($team, $users);
		$users     = $users->sortByDesc('total_reaction_count');
		$emojis    = ReactionCollection::getReactionsAndReactionAliasesByTeam($team, true);

		$this->_layout->team = $team;
		$this->_layout->content = view('team.leaderboard', [
			'team'   => $team,
			'users'  => $users,
			'emojis' => $emojis
		]);

		return $this->_layout;
	}
}