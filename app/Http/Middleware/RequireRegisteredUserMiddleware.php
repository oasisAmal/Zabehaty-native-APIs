<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireRegisteredUserMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        
        if (!$user) {
            return responseErrorMessage(__('auth::messages.user_not_found'), 401);
        }

        if ($user->isGuest()) {
            return responseErrorMessage(__('auth::messages.guest_cannot_create_order'), 403);
        }

        return $next($request);
    }
}
