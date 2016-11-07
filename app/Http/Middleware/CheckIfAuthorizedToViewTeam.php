<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\User;

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
        $team_domain = $request->team_domain;

        if (!$team_domain) {
            return $next($request);
        }

        $current_user = session()->get('user') ?: new User();

        $current_user_is_banned_from_looking_at_this_team_page = (
            !$current_user->isLoggedIn() ||
            !$current_user->team ||
            $current_user->team->domain !== $team_domain
        );

        if ($current_user_is_banned_from_looking_at_this_team_page) {
            if ($current_user->team && $current_user->team->domain) {
                return redirect()->action(
                    'TeamController@showLeaderboardAction', ['domain' => $current_user->team->domain]
                );
            }

            return redirect()->action('SlackController@guestHomepageAction');
        }

        return $next($request);
    }
}