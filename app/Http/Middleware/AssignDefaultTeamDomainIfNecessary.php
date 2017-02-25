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
        $team_domain = $request->input(Team::TEAM_DOMAIN_KEY);

        if (!$team_domain/* && !$user->isLoggedIn()*/) {
            $request->merge([Team::TEAM_DOMAIN_KEY => Team::DEMO_TEAM_DOMAIN]);
            $request->route()->setParameter(Team::TEAM_DOMAIN_KEY, Team::DEMO_TEAM_DOMAIN);
            $team_domain = Team::DEMO_TEAM_DOMAIN;
        }

        $user = User::getFromSession();
        $is_demo_mode = $team_domain === Team::DEMO_TEAM_DOMAIN && !$user->isLoggedIn();
        App::setIsDemoMode($is_demo_mode);

        return $next($request);
    }
}