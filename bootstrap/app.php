<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Middleware\AuthOptional;
use App\Http\Middleware\Localization;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->api(prepend: [
            Localization::class,
        ]);

        $middleware->alias([
            'auth-optional' => AuthOptional::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            if ($request->is('api/*')) {
                Log::error([
                    'message' => $e->getMessage(),
                    'route' => $request->route(),
                    'route_url' => $request->url(),
                    'request' => $request->all(),
                ]);
                return responseErrorMessage(__('messages.not_found'), 404);
            }
        });

        $exceptions->render(function (Throwable $e, Request $request) {
            if ($request->is('api/*')) {
                return responseErrorMessage($e->getMessage(), 500);
            }
        });
    })->create();
