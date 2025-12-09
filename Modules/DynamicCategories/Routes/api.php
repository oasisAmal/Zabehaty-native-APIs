<?php

use Illuminate\Support\Facades\Route;
use Modules\DynamicCategories\App\Http\Controllers\DynamicCategoriesController;

Route::prefix('dynamic-categories')->as('dynamic-categories.')->controller(DynamicCategoriesController::class)->group(function () {
    Route::group(['middleware' => ['auth:api']], function () {
        Route::post('/', 'index');
    });
});
