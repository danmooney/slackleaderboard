<?php

namespace App\Http\Middleware;

use Closure;

class RedirectIfMultipleConsecutiveSlashesInPath
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
        $path = $request->getPathInfo();

        $has_multiple_consecutive_slashes = preg_match('#/{2,}#', $path);

        if ($has_multiple_consecutive_slashes) {
            $path_without_multiple_slashes = preg_replace('#/{2,}#', '/', $path);
            return redirect($path_without_multiple_slashes, 301);
        }

        return $next($request);
    }
}