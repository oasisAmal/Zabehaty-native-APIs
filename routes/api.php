<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AppController;

Route::prefix('app')->as('app.')->controller(AppController::class)->group(function () {
    Route::get('get-app-countries', 'getAppCountries')->name('get-app-countries');
    Route::get('get-mobile-countries', 'getMobileCountries')->name('get-mobile-countries');

    Route::prefix('config')->as('config.')->group(function () {
        Route::get('onboarding-settings', 'getOnboardingSettings')->name('get-onboarding-settings');
    });
});
