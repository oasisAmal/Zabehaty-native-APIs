<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Automatically eager load relations for all models
        // Model::automaticallyEagerLoadRelationships();
        
        // Prevent Lazy Loading for all models in production
        // Model::preventLazyLoading(!app()->isProduction());

        // RateLimiter::for('api', function (Request $request) {
        //     return Limit::perSecond(1)->by($request->ip());
        // });
    }
}
