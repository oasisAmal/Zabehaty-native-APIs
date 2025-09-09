<?php

namespace App\Http\Middleware;

use Closure;
use App\Enums\AppName;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AppNameMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $app_name = '';
        if (env('APP_NAME') == AppName::HALAL_APP) {
            $app_name = AppName::HALAL_APP;
        } else {
            $app_name = AppName::ZABEHATY_APP;
        }

        $request->merge(['app_name' => $app_name]);

        return $next($request);
    }
}
