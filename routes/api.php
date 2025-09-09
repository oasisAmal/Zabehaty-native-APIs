<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AppController;

Route::prefix('app')->as('app.')->controller(AppController::class)->group(function () {
    Route::get('get-available-countries', 'getAvailableCountries')->name('get-available-countries');
    Route::get('get-auth-options', 'getAuthOptions');
});
