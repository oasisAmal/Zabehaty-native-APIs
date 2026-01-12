<?php

use Illuminate\Support\Facades\Route;
use Modules\DynamicShops\App\Http\Controllers\DynamicShopsController;

Route::prefix('dynamic-shops')->as('dynamic-shops.')->controller(DynamicShopsController::class)->group(function () {
    Route::group(['middleware' => ['auth:api']], function () {
        Route::get('/', 'index');
    });
});
