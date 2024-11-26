<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\BackpanelController;
use App\Http\Controllers\Admin\AppReleasesController;
use App\Http\Controllers\Admin\BackpanelAccessController;

Route::middleware(['auth:api', 'email_verified', 'is_admin'])->group(function () {
    
    //* Dashboard
    Route::get('/admin-backpanel', [BackpanelController::class, 'loadDashboard'])
        ->name('admin.backpanel');

    //* Access Management
    Route::get('/get-app-prices', [BackpanelAccessController::class, 'loadPrices'])
        ->name('get.app.prices');
    Route::post('/update-app-price', [BackpanelAccessController::class, 'updatePrice'])
        ->name('update.app.price');
    Route::get('/get-app-user-access', [BackpanelAccessController::class, 'loadUserAccess'])
        ->name('get.app.user.access');
    Route::post('/update-app-user-access', [BackpanelAccessController::class, 'updateUserAccess'])
        ->name('update.app.user.access');

    //* Releasemanagement
    Route::get('/get-app-releases/all', [AppReleasesController::class, 'loadAllReleases'])
        ->name('get.app.release.details.all');
    Route::post('/create-app-release', [AppReleasesController::class, 'create'])
        ->name('create.app.release');
    Route::post('/update-app-release', [AppReleasesController::class, 'update'])
        ->name('update.app.release');
    Route::delete('/delete-app-release/{id}', [AppReleasesController::class, 'delete'])
        ->name('delete-app-release');
});
