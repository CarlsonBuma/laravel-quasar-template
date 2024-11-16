<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Public\AppController;
use App\Http\Controllers\Public\ContentController;

//* App content
Route::get('/get-app-releases', [ContentController::class, 'loadIndexedReleases'])
    ->name('get.app.release');

//* App attributes
Route::get('/get-app-languages', [AppController::class, 'loadLanguages'])
    ->name('get.app.language');
Route::get('/get-app-countries', [AppController::class, 'loadCountries'])
    ->name('get.app.countries');

//* Import Routes
if(env('ALLOW_USER_AUTH', false)) {
    require __DIR__.'/api/auth.php';
    require __DIR__.'/api/user.php';
    require __DIR__.'/api/admin.php';
    require __DIR__.'/api/entity.php';
} 
    
