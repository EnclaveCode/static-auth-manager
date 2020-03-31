<?php

namespace EnclaveCode\StaticAuthManager\Middleware;

use Closure;
use Illuminate\Http\Request;

class HasRoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string $roles
     * @return mixed
     */
    public function handle(Request $request, Closure $next, string $roles)
    {
        if (auth()->guest()) {
            abort(403);
        }

        $roles = collect(explode('|', $roles));
        $user = auth()->user();

        if (!$user->hasRole($roles)) {
            abort(403);
        }

        return $next($request);
    }
}
