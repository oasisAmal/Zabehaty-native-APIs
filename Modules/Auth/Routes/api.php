<?php

use Illuminate\Support\Facades\Route;
use Modules\Auth\App\Http\Controllers\AuthController;

Route::prefix('auth')->as('auth.')->controller(AuthController::class)->group(function () {
    Route::post('register', 'register');
    Route::post('reset-password', 'resetPassword');
    Route::post('login', 'login');

    Route::prefix('otp')->as('otp.')->group(function () {
        Route::post('send', 'sendOtp');
        Route::post('verify', 'verifyOtp');
    });

    Route::group(['middleware' => ['auth:api']], function () {
        Route::post('refresh-token', 'refreshToken');
        Route::post('change-password', 'changePassword');
        Route::post('logout', 'logout');
    });
});
