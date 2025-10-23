<?php

use Illuminate\Support\Facades\Route;
use Modules\HomePage\App\Http\Controllers\HomePageController;

Route::prefix('home-page')->as('home-page.')->controller(HomePageController::class)->group(function () {
    Route::group(['middleware' => ['auth-optional']], function () {
        Route::get('/', 'index');
    });
});
