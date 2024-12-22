<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\BackpanelController;
use App\Http\Controllers\Admin\AppNewsfeedController;
use App\Http\Controllers\Access\AdminAccessController;

Route::middleware(['auth:api', 'email_verified', 'access_admin'])->group(function () {
    
    //* Dashboard
    Route::get('/admin-backpanel', [BackpanelController::class, 'loadDashboard'])
        ->name('admin.backpanel');

    //* Access Management
    Route::get('/get-app-prices', [AdminAccessController::class, 'loadPrices'])
        ->name('get.app.prices');
    Route::post('/update-app-price', [AdminAccessController::class, 'updatePrice'])
        ->name('update.app.price');
    Route::get('/get-app-user-access', [AdminAccessController::class, 'loadUserAccess'])
        ->name('get.app.user.access');
    Route::post('/update-app-user-access', [AdminAccessController::class, 'updateUserAccess'])
        ->name('update.app.user.access');
    Route::post('/create-app-user-access', [AdminAccessController::class, 'createUserAccess'])
        ->name('create.app.user.access');

    //* Releasemanagement
    Route::get('/get-app-newsfeed/all', [AppNewsfeedController::class, 'loadEntries'])
        ->name('get.app.newsfeed.all');
    Route::post('/create-app-newsfeed', [AppNewsfeedController::class, 'create'])
        ->name('create.app.newsfeed');
    Route::post('/update-app-newsfeed', [AppNewsfeedController::class, 'update'])
        ->name('update.app.newsfeed');
    Route::delete('/delete-app-newsfeed/{id}', [AppNewsfeedController::class, 'delete'])
        ->name('delete.app.newsfeed');
});
