<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;
use Modules\Search\Database\Seeders\SearchDatabaseSeeder;

Route::get('search/test-seeder', function () {
    Artisan::call('db:seed', [
        '--class' => SearchDatabaseSeeder::class,
        '--force' => true,
    ]);

    return response()->json([
        'message' => 'Search seeder executed successfully.',
        'output' => Artisan::output(),
    ]);
});
