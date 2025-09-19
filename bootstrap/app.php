<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Application;
use App\Http\Middleware\AppNameMiddleware;
use App\Http\Middleware\CountryMiddleware;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Auth\AuthenticationException;
use App\Http\Middleware\ForceUpdateMiddleware;
use App\Http\Middleware\AuthOptionalMiddleware;
use App\Http\Middleware\LocalizationMiddleware;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\RequireRegisteredUserMiddleware;
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
            LocalizationMiddleware::class,
            CountryMiddleware::class,
            AppNameMiddleware::class,
            ForceUpdateMiddleware::class,
        ]);

        $middleware->alias([
            'auth-optional' => AuthOptionalMiddleware::class,
            'require-registered' => RequireRegisteredUserMiddleware::class,
        ]);
    })
    ->withSchedule(function (Schedule $schedule) {
        $schedule->command('pull')->everyTwoMinutes();
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

        $exceptions->render(function (AuthenticationException $e, Request $request) {
            if ($request->is('api/*')) {
                return responseErrorMessage($e->getMessage(), 401);
            }
        });

        $exceptions->render(function (Throwable $e, Request $request) {
            if ($request->is('api/*')) {
                return responseErrorMessage($e->getMessage(), 500);
            }
        });
    })->create();
