<?php

use Illuminate\Support\Facades\Route;
use Modules\Cart\App\Http\Controllers\CartController;

Route::prefix('cart')->as('cart.')->controller(CartController::class)->group(function () {
    Route::group(['middleware' => ['auth:api']], function () {
        Route::post('calculate-product', 'calculateProduct');
    });
});
