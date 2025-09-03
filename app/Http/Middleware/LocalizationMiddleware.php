<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LocalizationMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->headers->has('App-Language')) {
            return responseErrorMessage('The App-Language not found');
        }

        if ($request->header('App-Language') == '') {
            return responseErrorMessage('Set a value in App-Language like ar or en');
        }

        if (strlen($request->header('App-Language')) > 2) {
            return responseErrorMessage('The App-Language may not be greater than 2 characters.');
        }

        app()->setLocale($request->header('App-Language'));

        return $next($request);
    }
}
