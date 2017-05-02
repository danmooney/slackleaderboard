<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\User;
use App\Models\Team;
use App;

class CheckIfAuthorizedToViewTeam
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        $team_domain = $request->route(Team::TEAM_DOMAIN_KEY);

        if (!$team_domain || App::getDemoMode()) {
            return $next($request);
        }

        $current_user = User::getFromSession();

        $current_user_is_banned_from_looking_at_this_team_page = (
            !$current_user->isLoggedIn() ||
            !$current_user->team ||
            $current_user->team->domain !== $team_domain
        );

        $no_segments_exist_in_url = $request->getPathInfo() === '/';

        if ($current_user_is_banned_from_looking_at_this_team_page || $no_segments_exist_in_url) {
            if ($current_user->team && $current_user->team->domain) {
                return redirect()->action(
                    'TeamController@showLeaderboardAction', ['domain' => $current_user->team->domain]
                );
            }

            if ($request->wantsJson()) {
                return response()->json(['error' => true, 'message' => 'Not authorized to view team'], 403);
            }

            return redirect()->to('/');
        }

        return $next($request);
    }
}
