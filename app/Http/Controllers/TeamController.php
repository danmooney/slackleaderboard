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
        if (!isset($_COOKIE['slack'])) {
            App::abort(404);
        }

        $team = Team::where(['domain' => $team_domain])->first();

        if (!$team) {
            App::abort(404);
        }

        $users = User::where(['team_id' => $team->team_id])->get();

        PostUserReactionCollection::getTotalReactionGivenCountsByEachUserOnTeamAndAddToUsers($team, $users);
        PostUserReactionCollection::getAllPostUserReactionsByEachUserOnTeamAndAddToUsers($team, $users);
		$users = $users->sort(function ($a, $b) {
			if ($a->total_reaction_count !== $b->total_reaction_count) {
				return $b->total_reaction_count - $a->total_reaction_count; // sort by total reaction count desc
			}

			return strcasecmp($a->name, $b->name); // sort by name asc
		});

        $emojis    = ReactionCollection::getReactionsAndReactionAliasesByTeam($team, true);

        $single_user_reaction_received_counts = PostUserReactionCollection::getCountsOfReactionsReceivedToASingleUsersPostsGroupedByUser($team, $users);
//        $single_user_reaction_received_counts = $single_user_reaction_received_counts->sortByDesc('total_reaction_count');

        $this->_layout->team = $team;
        $this->_layout->content = view('team.leaderboard', [
            'team'   => $team,
            'users'  => $users,
            'emojis' => $emojis,
            'single_user_reaction_received_counts' => $single_user_reaction_received_counts
        ]);

        return $this->_layout;
    }
}