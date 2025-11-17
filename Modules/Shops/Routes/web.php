<?php

use Illuminate\Support\Facades\Route;
use Modules\Shops\App\Http\Controllers\ShopsController;

Route::middleware(['auth'])->group(function () {
    Route::resource('shops', ShopsController::class)->names('shops');
});
