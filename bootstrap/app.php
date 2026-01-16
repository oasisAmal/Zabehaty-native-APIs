<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Application;
use App\Http\Middleware\AppNameMiddleware;
use App\Http\Middleware\CountryMiddleware;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Auth\AuthenticationException;
use App\Http\Middleware\ForceUpdateMiddleware;
use App\Http\Middleware\AddressStateMiddleware;
use App\Http\Middleware\AuthOptionalMiddleware;
use App\Http\Middleware\LocalizationMiddleware;
use App\Providers\SocialiteAppleServiceProvider;
use App\Http\Middleware\CheckGuestModeMiddleware;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\RequireRegisteredUserMiddleware;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withProviders([
        SocialiteAppleServiceProvider::class,
    ])
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
            CheckGuestModeMiddleware::class,
            AddressStateMiddleware::class,
            'throttle:api',
        ]);

        $middleware->alias([
            'auth-optional' => AuthOptionalMiddleware::class,
            'require-registered' => RequireRegisteredUserMiddleware::class,
            'address-state' => AddressStateMiddleware::class,
        ]);
    })
    ->withSchedule(function (Schedule $schedule) {
        $schedule->command('git:pull')->everyTwoMinutes();
        $schedule->command('sanctum:prune-expired --hours=24')->daily();
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            if ($request->is('api/*')) {
                return responseErrorMessage(__('messages.not_found'), 404);
            }
        });

        $exceptions->render(function (AuthenticationException $e, Request $request) {
            if ($request->is('api/*')) {
                return responseErrorMessage($e->getMessage(), 401);
            }
        });

        $exceptions->render(function (ThrottleRequestsException $e, Request $request) {
            if ($request->is('api/*')) {
                return responseErrorMessage(__('auth.throttle'), 429);
            }
        });

        $exceptions->render(function (Throwable $e, Request $request) {
            if ($request->is('api/*')) {
                Log::error('Exception in API', [
                    'message' => $e->getMessage(),
                    'route' => $request->route(),
                    'route_url' => $request->url(),
                    'request' => $request->all(),
                ]);
                return responseErrorMessage($e->getMessage(), 500);
            }
        });
    })->create();
