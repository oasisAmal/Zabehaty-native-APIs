<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Laravel\Socialite\Contracts\Factory as SocialiteFactory;

class SocialiteAppleServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(SocialiteFactory $socialite): void
    {
        $socialite->extend('apple', function ($app) use ($socialite) {
            return $socialite->buildProvider(
                \SocialiteProviders\Apple\Provider::class,
                config('services.apple')
            );
        });
    }
}
