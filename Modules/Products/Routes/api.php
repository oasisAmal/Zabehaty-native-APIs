<?php

use Illuminate\Support\Facades\Route;
use Modules\Products\App\Http\Controllers\ProductController;

Route::prefix('products')->as('products.')->controller(ProductController::class)->group(function () {
    Route::group(['middleware' => ['auth:api']], function () {
        Route::post('/', 'index');
    });
});
