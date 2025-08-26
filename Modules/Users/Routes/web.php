<?php

use Illuminate\Support\Facades\Route;
use Modules\Users\App\Http\Controllers\UsersController;

Route::middleware(['auth'])->group(function () {
    Route::resource('users', UsersController::class)->names('users');
});
