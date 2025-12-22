<?php

use Illuminate\Support\Facades\Route;
use Modules\Shops\App\Http\Controllers\ShopController;

Route::prefix('shops')->as('shops.')->controller(ShopController::class)->group(function () {
    Route::group(['middleware' => ['auth:api']], function () {
        Route::get('/detail/{id}', 'detail');
        Route::post('/', 'index');
    });
});
