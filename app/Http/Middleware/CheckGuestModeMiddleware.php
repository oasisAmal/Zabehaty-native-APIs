<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Settings;

class CheckGuestModeMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth('api')->user();
        $settings = Settings::where('key', 'guest_mode')->first();
        $guestMode = $settings ? (bool) $settings->value : false;

        if ($user && $user->isGuest() && $guestMode == false) {
            $user->forceDelete();
            return responseErrorMessage(__('auth::messages.guest_mode_not_allowed'), 401);
        }

        return $next($request);
    }
}
