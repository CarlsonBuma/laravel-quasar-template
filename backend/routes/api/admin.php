<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\BackpanelController;
use App\Http\Controllers\Admin\AppReleasesController;


Route::middleware(['auth:api', 'email_verified', 'is_admin'])->group(function () {
    
    //* Dashboard
    Route::get('/admin-backpanel', [BackpanelController::class, 'loadDashboard'])
        ->name('admin.backpanel');

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

//* Public Accessible Data
Route::get('/get-app-releases/{index}', [AppReleasesController::class, 'loadIndexedReleases'])
    ->name('get.app.release');
