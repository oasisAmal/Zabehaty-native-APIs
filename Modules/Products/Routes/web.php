<?php

use Illuminate\Support\Facades\Route;
use Modules\Products\App\Http\Controllers\ProductsController;

Route::middleware(['auth'])->group(function () {
    Route::resource('products', ProductsController::class)->names('products');
});
