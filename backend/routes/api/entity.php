<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserEntity\EntityProfileController;


Route::middleware(['auth:api', 'email_verified', 'access_cockpit'])->group(function () {

    //* Manage entity profile
    Route::get('/load-entity-profile', [EntityProfileController::class, 'loadProfile'])
        ->name('load.entity.profile');
    Route::post('/update-entity-public-access', [EntityProfileController::class, 'updatePublicity'])
        ->name('update.entity.public.access');
    Route::post('/update-entity-avatar', [EntityProfileController::class, 'updateAvatar'])
        ->name('update.entity.avatar');
    Route::post('/update-entity-credits', [EntityProfileController::class, 'updateCredentials'])
        ->name('update.entity.credits');
    Route::post('/update-entity-impressum', [EntityProfileController::class, 'updateImpressum'])
        ->name('update.entity.impressum');
    Route::post('/update-entity-about', [EntityProfileController::class, 'updateAbout'])
        ->name('update.entity.about');
    Route::post('/update-entity-bulletpoints', [EntityProfileController::class, 'updateTags'])
        ->name('update.entity.bulletpoints');
    Route::post('/update-entity-location', [EntityProfileController::class, 'updateLocation'])
        ->name('update.entity.location');
});
