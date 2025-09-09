<?php

use Illuminate\Support\Facades\Route;
use Modules\Notifications\App\Http\Controllers\NotificationsController;

Route::prefix('notifications')->as('notifications.')->controller(NotificationsController::class)->group(function () {
    Route::group(['middleware' => ['auth:api']], function () {
        Route::get('list', 'list');
        Route::post('read/{id}', 'read');
        Route::post('read-all', 'readAll');
        Route::get('unread-count', 'unreadCount');
    });
});
