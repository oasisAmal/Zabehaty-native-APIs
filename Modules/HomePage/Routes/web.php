<?php

use Illuminate\Support\Facades\Route;
use Modules\HomePage\App\Http\Controllers\HomePageController;

Route::middleware(['auth:api'])->group(function () {
    Route::resource('homepages', HomePageController::class)->names('homepage');
});
