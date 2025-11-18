<?php

use Illuminate\Support\Facades\Route;
use Modules\Products\App\Http\Controllers\ProductsController;

Route::prefix('products')->as('products.')->controller(ProductsController::class)->group(function () {
    Route::group(['middleware' => ['auth:api']], function () {
        Route::post('/', 'index');
    });
});
