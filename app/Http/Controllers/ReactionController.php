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
        if (!isset($_COOKIE['slack'])) {
            App::abort(404);
        }

        $team = Team::where(['domain' => $team_domain])->first();

        if (!$team) {
            App::abort(404);
        }

        $users    = User::where(['team_id' => $team->team_id/*, 'handle' => $user_handle*/])->get();
        $reaction = Reaction::getReactionByAlias($reaction_alias);

        PostUserReactionCollection::getTotalReactionCountsByEachUserOnTeamAndAddToUsers($team, $users);
        $reaction_counts_by_users = PostUserReactionCollection::getEmojiReactionCountByReactionGroupedByAllUsers($reaction, $users);
        $reaction_counts_by_users = $reaction_counts_by_users->sortByDesc('total_count_using_this_reaction');

        $total_count = 0;
        foreach ($reaction_counts_by_users as $reaction_user) {
            $total_count += $reaction_user->total_count_using_this_reaction;
        }

        $this->_layout->team = $team;
        $this->_layout->content = view('reaction.leaderboard', [
            'team'   => $team,
            'users'  => $users,
            'reaction'  => $reaction,
            'reaction_counts_by_users' => $reaction_counts_by_users,
            'total_count' => $total_count
        ]);

        return $this->_layout;
    }
}