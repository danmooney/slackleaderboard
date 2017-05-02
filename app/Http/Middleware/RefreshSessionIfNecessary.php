<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\User;

class RefreshSessionIfNecessary
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
        if (!$request->isMethod('get')) {
            return $next($request);
        }

        $user = User::getFromSession();

        if ($user->needsToRefreshSession()) {
            $user->refreshSession();
        }

        return $next($request);
    }
}
