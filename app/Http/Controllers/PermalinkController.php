<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Team;
use App\Models\Reaction;
use App\Collections\PostUserReaction;
use App;

class PermalinkController extends Controller
{
    public function fetchAction($team_domain, Request $request)
    {
        $team = Team::where(['domain' => $team_domain])->first();

        if (!$team) {
            App::abort(404);
        }

        $reaction_id = $request->input('reaction_id');
        $giver_user_ids = array_filter(explode('|', $request->input('giver_user_id')));
        $receiver_user_id = $request->input('receiver_user_id');

        $page     = $request->input('page', 1);

        if (!is_numeric($page)) {
            App::abort(422, 'Page parameter must be numeric');
        }

        if (count($giver_user_ids) > 2) {
            App::abort(422, 'Maximum number of giver_user_id parameters accepted is 2');
        }

        $reaction = $giver_users = $receiver_user = null;

        if ($reaction_id) {
            $reaction = Reaction::find($reaction_id);
            if (!$reaction || !in_array($reaction->team_id, [null, $team->getKey()])) {
                App::abort(404);
            }
        }

        if ($giver_user_ids) {
            $giver_users = User::where(['team_id' => $team->getKey()])->whereIn('user_id', $giver_user_ids)->get();

            if (count($giver_users) !== count($giver_user_ids)) {
                App::abort(404);
            }

            $giver_users = $giver_users->filter(function ($giver_user) {
                return $giver_user->isEligibleToBeOnLeaderBoard();
            });

            if (!count($giver_users)) {
                App::abort(404);
            }
        }

        if ($receiver_user_id) {
            $receiver_user = User::find($receiver_user_id);

            if (!$receiver_user || !$receiver_user->isOnTeam($team) || !$receiver_user->isEligibleToBeOnLeaderBoard()) {
                App::abort(404);
            }
        }

        $rows = PostUserReaction::getPermalinksToReactionByGiverUser($reaction, $giver_users, $receiver_user, $page);

        if ($request->wantsJson()) {
            // TODO
        }

        return response()->json($rows->take(3)->pluck('url'));
    }
}