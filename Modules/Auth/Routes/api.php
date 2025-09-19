<?php

use Illuminate\Support\Facades\Route;
use Modules\Auth\App\Http\Controllers\AuthController;

Route::prefix('auth')->as('auth.')->controller(AuthController::class)->group(function () {
    Route::group(['middleware' => ['auth-optional:api']], function () {
        Route::post('register', 'register');
    });

    Route::post('login', 'login');
    Route::post('create-guest', 'createGuest');

    Route::prefix('otp')->as('otp.')->group(function () {
        Route::post('send', 'sendOtp');
        Route::post('verify', 'verifyOtp');
    });

    Route::group(['middleware' => ['auth-optional:api']], function () {
        Route::post('change-password', 'changePassword');
    });

    Route::group(['middleware' => ['auth:api']], function () {
        Route::post('refresh-token', 'refreshToken');
        Route::post('logout', 'logout');
        Route::get('profile', 'profile');
        Route::post('delete-account', 'deleteAccount');
    });
});
