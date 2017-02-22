<?php

namespace App\Http\Controllers;

use App\Models\ReactionAlias;
use App\Models\Team;
use App\Models\User;
use App\Models\Reaction;
use App\Models\Post;
use App\Collections\PostUserReaction as PostUserReactionCollection;
use App;
use DB;

class ReactionController extends Controller
{
    public function showLeaderboardAction($team_domain, $reaction_alias)
    {
        $team = Team::where(['domain' => $team_domain])->first();

        if (!$team) {
            App::abort(404);
        }

        $users    = User::where(['team_id' => $team->team_id/*, 'handle' => $user_handle*/])->get();
        $reaction = Reaction::getReactionByAlias($reaction_alias, $team);

        if (!$reaction) {
            App::abort(404);
        }

        PostUserReactionCollection::getTotalReactionGivenCountsByEachUserOnTeamAndAddToUsers($team, $users);

        $reaction_given_counts_by_users = PostUserReactionCollection::getEmojiReactionGivenCountByReactionGroupedByAllUsers($reaction, $users);
        $reaction_given_counts_by_users = $reaction_given_counts_by_users->sortByDesc('total_count_using_this_reaction');

        $total_count = 0;
        foreach ($reaction_given_counts_by_users as $reaction_user) {
            $total_count += $reaction_user->total_count_using_this_reaction;
        }

        $reaction_received_counts_by_users = PostUserReactionCollection::getEmojiReactionReceivedCountByReactionGroupedByAllUsers($reaction, $users);
        $reaction_received_counts_by_users = $reaction_received_counts_by_users->sortByDesc('total_count_using_this_reaction');

        PostUserReactionCollection::getCountsOfReactionsReceivedToASingleUsersPostsGroupedByUser($team, $users);

        $this->_layout->team = $team;
        $this->_layout->content = view('reaction.leaderboard', [
            'team'   => $team,
            'users'  => $users,
            'reaction'  => $reaction,
            'reaction_given_counts_by_users' => $reaction_given_counts_by_users,
            'reaction_received_counts_by_users' => $reaction_received_counts_by_users,
            'total_count' => $total_count
        ]);

        return $this->_layout;
    }
}