<?php

namespace App\Http\Middleware;

use Closure;
use App\Enums\AppCountries;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CountryMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (in_array($request->route()->getName(), $this->getExcludedRoutes())) {
            return $next($request);
        }

        if (!$request->headers->has('App-Country')) {
            return responseErrorMessage('The App-Country not found');
        }

        if ($request->header('App-Country') == '') {
            return responseErrorMessage('Set a value in App-Country like AE or SA or OM');
        }

        if (!in_array($request->header('App-Country'), AppCountries::getValues())) {
            return responseErrorMessage('The App-Country may not be valid.');
        }

        $request->merge(['app_country_code' => $request->header('App-Country')]);

        return $next($request);
    }

    private function getExcludedRoutes()
    {
        return [
            'app.get-app-countries',
            'app.get-mobile-countries',
            'app.config.get-onboarding-settings'
        ];
    }
}
