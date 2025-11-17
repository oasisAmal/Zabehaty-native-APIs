<?php

use Illuminate\Support\Facades\Route;
use Modules\Shops\App\Http\Controllers\ShopsController;

Route::prefix('shops')->as('shops.')->controller(ShopsController::class)->group(function () {
    Route::group(['middleware' => ['auth:api', 'require-registered']], function () {
        //
    });
});
