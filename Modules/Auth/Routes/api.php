<?php

use Illuminate\Support\Facades\Route;
use Modules\Auth\App\Http\Controllers\AuthController;

Route::prefix('auth')->as('auth.')->controller(AuthController::class)->group(function () {
    Route::post('login', 'login')->name('login');

    Route::group(['middleware' => ['auth:api']], function () {
        Route::post('refresh-token', 'refreshToken')->name('refresh-token');
    });
});
