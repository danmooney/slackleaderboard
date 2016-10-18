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

        PostUserReactionCollection::getTotalReactionCountsByEachUserOnTeamAndAddToUsers($team, $users);
        PostUserReactionCollection::getAllPostUserReactionsByEachUserOnTeamAndAddToUsers($team, $users);
        $users     = $users->sortByDesc('total_reaction_count');
        $emojis    = ReactionCollection::getReactionsAndReactionAliasesByTeam($team, true);

        $single_user_reaction_counts = PostUserReactionCollection::getCountsOfReactionsToASingleUsersPostsGroupedByUser($team, $users);
//        $single_user_reaction_counts = $single_user_reaction_counts->sortByDesc('total_reaction_count');

        $this->_layout->team = $team;
        $this->_layout->content = view('team.leaderboard', [
            'team'   => $team,
            'users'  => $users,
            'emojis' => $emojis,
            'single_user_reaction_counts' => $single_user_reaction_counts
        ]);

        return $this->_layout;
    }
}