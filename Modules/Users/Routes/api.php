<?php

use Illuminate\Support\Facades\Route;
use Modules\Users\App\Http\Controllers\UsersController;

Route::prefix('users')->as('users.')->controller(UsersController::class)->group(function () {
    Route::group(['middleware' => ['auth:api', 'require-registered']], function () {
        //
    });
});
