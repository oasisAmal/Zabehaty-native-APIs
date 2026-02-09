<?php

use Illuminate\Support\Facades\Route;
use Modules\Users\App\Http\Controllers\UserController;
use Modules\Users\App\Http\Controllers\AddressController;
use Modules\Users\App\Http\Controllers\UserCountController;

Route::prefix('users')->as('users.')->middleware(['auth:api', 'require-registered'])->controller(UserController::class)->group(function () {});

Route::prefix('addresses')->as('addresses.')->middleware(['auth:api'])->controller(AddressController::class)->group(function () {
    Route::post('store', 'store')->name('store');
    Route::post('update/{id}', 'update')->name('update');
    Route::get('detail/{id}', 'detail')->name('detail');
    Route::group(['middleware' => ['auth:api', 'require-registered']], function () {
        Route::delete('delete/{id}', 'destroy')->name('destroy');
        Route::post('set-default', 'setDefault')->name('set-default');
        Route::get('get', 'index')->name('index');
    });
});

Route::prefix('users')->as('users.')->middleware(['auth:api'])->controller(UserCountController::class)->group(function () {
    Route::get('counts', 'index')->name('counts');
});
