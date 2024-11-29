<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserCockpit\CockpitController;


Route::middleware(['auth:api', 'email_verified', 'access_cockpit'])->group(function () {

    //* Manage cockpit profile
    Route::get('/load-cockpit-profile', [CockpitController::class, 'loadProfile'])
        ->name('load.cockpit.profile');
    Route::post('/update-cockpit-public-access', [CockpitController::class, 'updatePublicity'])
        ->name('update.cockpit.public.access');
    Route::post('/update-cockpit-avatar', [CockpitController::class, 'updateAvatar'])
        ->name('update.cockpit.avatar');
    Route::post('/update-cockpit-credits', [CockpitController::class, 'updateName'])
        ->name('update.cockpit.credits');
    Route::post('/update-cockpit-impressum', [CockpitController::class, 'updateImpressum'])
        ->name('update.cockpit.impressum');
    Route::post('/update-cockpit-about', [CockpitController::class, 'updateAbout'])
        ->name('update.cockpit.about');
    Route::post('/update-cockpit-bulletpoints', [CockpitController::class, 'updateTags'])
        ->name('update.cockpit.bulletpoints');
    Route::post('/update-cockpit-location', [CockpitController::class, 'updateLocation'])
        ->name('update.cockpit.location');
});
