<?php

namespace App\Http\Middleware;

use Closure;
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

        // if no team domain exists, or user is using the demo team facade, set the appropritate demo team on the route/request objects
        if (!$team_domain || $team_domain === TEAM::DEMO_TEAM_DOMAIN_FACADE) {
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