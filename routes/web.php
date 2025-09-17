<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/git-pull', function () {
    shell_exec("git status 2>&1");
    $pull = shell_exec("git pull 2>&1");
    return $pull;
});

Route::get('/migrate', function () {
    Artisan::call('migrate');
    return Artisan::output();
});
