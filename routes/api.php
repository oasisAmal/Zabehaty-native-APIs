<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AppController;
use App\Http\Controllers\ExampleOrderController;

Route::prefix('app')->as('app.')->controller(AppController::class)->group(function () {
    Route::get('get-app-countries', 'getAppCountries')->name('get-app-countries');
    Route::get('get-mobile-countries', 'getMobileCountries')->name('get-mobile-countries');
    Route::get('get-popups', 'getPopups')->name('get-popups');

    Route::prefix('config')->as('config.')->group(function () {
        Route::get('app-settings', 'getAppSettings')->name('get-app-settings');
        Route::get('onboarding-ads', 'getOnboardingAds')->name('get-onboarding-ads');
    });
});

// Example routes for guest/registered user functionality
Route::prefix('orders')->as('orders.')->controller(ExampleOrderController::class)->group(function () {
    // Cart operations (allowed for guests)
    Route::group(['middleware' => ['auth-optional:api']], function () {
        Route::post('add-to-cart', 'addToCart')->name('add-to-cart');
        Route::get('cart', 'getCart')->name('get-cart');
    });
    
    // Order operations (only for registered users)
    Route::group(['middleware' => ['auth:api', 'require-registered']], function () {
        Route::post('create', 'createOrder')->name('create');
    });
});
