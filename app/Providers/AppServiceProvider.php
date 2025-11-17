<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Model;
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
        Model::automaticallyEagerLoadRelationships();
        
        // Prevent Lazy Loading for all models in production
        Model::preventLazyLoading(!app()->isProduction());
    }
}
