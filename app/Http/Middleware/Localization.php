<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class Localization
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->headers->has('Language')) {
            return responseErrorMessage('The Language not found');
        }

        if ($request->header('Language') == '') {
            return responseErrorMessage('Set a value in Language like ar or en');
        }

        if (strlen($request->header('Language')) > 2) {
            return responseErrorMessage('The Language may not be greater than 2 characters.');
        }

        app()->setLocale($request->header('Language'));

        return $next($request);
    }
}
