<?php

use Illuminate\Support\Facades\Route;
use Modules\Search\Http\Controllers\SearchController;

Route::prefix('searches')->as('searches.')->controller(SearchController::class)->group(function () {
    Route::group(['middleware' => ['auth:api', 'require-registered']], function () {
        //
    });
});
