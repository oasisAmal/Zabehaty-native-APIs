<?php

use Illuminate\Support\Facades\Route;
use Modules\Categories\Http\Controllers\CategoriesController;

Route::prefix('categories')->as('categories.')->controller(CategoriesController::class)->group(function () {
    Route::group(['middleware' => ['auth:api', 'require-registered']], function () {
        //
    });
});
