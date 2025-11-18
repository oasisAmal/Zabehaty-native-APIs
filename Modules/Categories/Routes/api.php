<?php

use Illuminate\Support\Facades\Route;
use Modules\Categories\App\Http\Controllers\CategoryController;

Route::prefix('categories')->as('categories.')->controller(CategoryController::class)->group(function () {
    Route::group(['middleware' => ['auth:api']], function () {
        Route::post('/', 'index');
        //
    });
});
