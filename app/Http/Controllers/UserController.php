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
    public function loginAction()
    {
        $slack_oauth_url = config('app.slack_oauth_url');
        return redirect()->away($slack_oauth_url);
    }

    public function logoutAction()
    {
        session()->flush();
        return redirect()->action(
            'SlackController@guestHomepageAction'
        );
    }

    public function showLeaderboardAction($team_domain, $user_handle)
    {
        if (!isset($_COOKIE['slack'])) {
            App::abort(404);
        }
    
        $team = Team::where(['domain' => $team_domain])->first();
    
        if (!$team) {
            App::abort(404);
        }
    
        $users = User::where(['team_id' => $team->team_id/*, 'handle' => $user_handle*/])->get();
    
        PostUserReactionCollection::getTotalReactionCountsByEachUserOnTeamAndAddToUsers($team, $users);
    //        PostUserReactionCollection::getAllPostUserReactionsByEachUserOnTeamAndAddToUsers($team, $users);
    
        $user  = $users->where('handle', $user_handle)->first();
    
        if (!$user) {
            App::abort(404);
        }
    
        $emojis                                        = ReactionCollection::getReactionsAndReactionAliasesByTeam($team, true);
        $reactions_to_this_users_posts_grouped_by_user = PostUserReactionCollection::getTotalReactionsToASingleUsersPostsGroupedByAllUsers($user, $users);
    
        $total_mutual_reactions_for_this_user_by_user_id_and_reaction_id = PostUserReactionCollection::getMutualReactionsToASingleUsersReactions($user);
    
        $this->_layout->team = $team;
        $this->_layout->content = view('user.leaderboard', [
            'team'   => $team,
            'users'  => $users,
            'user'   => $user,
            'emojis' => $emojis,
            'reactions_to_this_users_posts_grouped_by_user' => $reactions_to_this_users_posts_grouped_by_user,
            'total_mutual_reactions_for_this_user_by_user_id_and_reaction_id' => $total_mutual_reactions_for_this_user_by_user_id_and_reaction_id
        ]);
    
        return $this->_layout;
    }
}