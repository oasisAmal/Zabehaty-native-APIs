<?php

use Illuminate\Support\Facades\Route;
use Modules\Search\App\Http\Controllers\SearchController;

Route::prefix('searches')->as('searches.')->controller(SearchController::class)->group(function () {
    Route::group(['middleware' => ['auth:api']], function () {
        Route::get('prepare', 'prepare');
        Route::post('suggestions', 'suggestions');
    });
});
