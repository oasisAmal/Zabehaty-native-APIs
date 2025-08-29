<?php

use Illuminate\Support\Facades\Route;
use Modules\Auth\App\Http\Controllers\AuthController;

Route::prefix('auth')->as('auth.')->controller(AuthController::class)->group(function () {
    Route::post('login', 'login')->name('login');
    Route::post('register', 'register')->name('register');
    Route::post('reset-password', 'resetPassword')->name('reset-password');

    Route::prefix('otp')->as('otp.')->controller(AuthController::class)->group(function () {
        Route::post('send', 'sendOtp')->name('send');
        Route::post('verify', 'verifyOtp')->name('verify');
    });

    Route::group(['middleware' => ['auth:api']], function () {
        Route::post('refresh-token', 'refreshToken')->name('refresh-token');
        Route::post('change-password', 'changePassword')->name('change-password');
        Route::post('logout', 'logout')->name('logout');
    });
});
