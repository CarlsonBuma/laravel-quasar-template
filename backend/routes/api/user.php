<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\User\UserAvatarController;


Route::middleware(['auth:api', 'email_verified'])->group(function () {

    //* Manage user avatar
    Route::get('/load-user-avatar', [UserAvatarController::class, 'loadAvatar'])
        ->name('load.user.avatar');
    Route::post('/update-user-avatar-publicity', [UserAvatarController::class, 'updatePublicity'])
        ->name('update.user.avatar.publicity');
    Route::post('/update-user-avatar-about', [UserAvatarController::class, 'updateAbout'])
        ->name('update.user.avatar.about');
    Route::post('/update-user-avatar-location', [UserAvatarController::class, 'updateLocation'])
        ->name('update.user.avatar.location');
    Route::post('/update-user-avatar-country', [UserAvatarController::class, 'updateCountry'])
        ->name('update.user.avatar.country');
});
