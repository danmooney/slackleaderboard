<?php

namespace App\Http\Middleware;

use Closure;
use App\Http\Controllers\TeamController;
use App\Models\Team;
use App\Models\User;
use App;

class AssignDefaultTeamDomainIfNecessary
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

        $team_domain_altered_to_be_demo_team = false;

        $team_leaderboard_route_name = TeamController::class . '@showLeaderboardAction';
        $is_on_team_leaderboard_route = request()->route()->getActionName() === $team_leaderboard_route_name;

        $demo_team_domain_is_only_segment_in_url_and_needs_to_redirect_back_to_homepage = (
            $team_domain === Team::DEMO_TEAM_DOMAIN_FACADE &&
            $is_on_team_leaderboard_route
        );

        if ($demo_team_domain_is_only_segment_in_url_and_needs_to_redirect_back_to_homepage) {
            return redirect()->to('/');
        }

        // if no team domain exists, or user is using the demo team facade, set the appropritate demo team on the route/request objects
        if (!$team_domain || $team_domain === Team::DEMO_TEAM_DOMAIN_FACADE) {
            $request->merge([Team::TEAM_DOMAIN_KEY => Team::DEMO_TEAM_DOMAIN]);
            $request->route()->setParameter(Team::TEAM_DOMAIN_KEY, Team::DEMO_TEAM_DOMAIN);
            $team_domain_altered_to_be_demo_team = true;
        }

        $user = User::getFromSession();
        $is_demo_mode = $team_domain_altered_to_be_demo_team && !$user->isLoggedIn();
        App::setDemoMode($is_demo_mode);

        return $next($request);
    }
}