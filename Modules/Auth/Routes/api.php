<?php

use Illuminate\Support\Facades\Route;
use Modules\Auth\App\Http\Controllers\AuthController;

Route::prefix('auth')->as('auth.')->controller(AuthController::class)->group(function () {
    Route::group(['middleware' => ['auth-optional:api', 'throttle:api:5,1']], function () {
        Route::post('register', 'register');
    });

    Route::post('check-mobile', 'checkMobile');
    Route::post('login', 'login')->middleware(['throttle:api:5,1']);
    Route::post('social-login', 'socialLogin')->middleware(['throttle:api:5,1']);
    Route::post('create-guest', 'createGuest')->middleware(['throttle:api:5,1']);

    Route::prefix('otp')->as('otp.')->middleware(['throttle:api:5,1'])->group(function () {
        Route::post('send', 'sendOtp');
        Route::post('verify', 'verifyOtp');
    });

    Route::group(['middleware' => ['auth-optional:api']], function () {
        // Route::post('change-password', 'changePassword');
    });

    Route::group(['middleware' => ['auth:api', 'throttle:api:5,1']], function () {
        Route::post('change-password', 'changePassword');
        Route::post('update-mobile', 'updateMobile');
        Route::post('refresh-token', 'refreshToken');
        Route::post('logout', 'logout');
        Route::get('profile', 'profile');
        Route::post('delete-account', 'deleteAccount');
    });
});
