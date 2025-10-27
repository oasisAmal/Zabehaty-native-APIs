<?php

use Illuminate\Support\Facades\Route;
use Modules\Users\App\Http\Controllers\UsersController;
use Modules\Users\App\Http\Controllers\AddressController;

Route::prefix('users')->as('users.')->middleware(['auth:api', 'require-registered'])->controller(UsersController::class)->group(function () {});

Route::prefix('addresses')->as('addresses.')->middleware(['auth:api'])->controller(AddressController::class)->group(function () {
    Route::post('store', 'store')->name('store');
    Route::group(['middleware' => ['auth:api', 'require-registered']], function () {
        Route::post('update/{id}', 'update')->name('update');
        Route::delete('delete/{id}', 'destroy')->name('destroy');
        Route::get('get', 'index')->name('index');
    });
});
