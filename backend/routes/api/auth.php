<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\UserAuthController;
use App\Http\Controllers\Auth\UserAccountController;
use App\Http\Controllers\Auth\UserProfileController;
use App\Http\Controllers\Auth\CreateAccountController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Auth\TransferAccountController;
use App\Http\Controllers\Auth\EmailVerificationController;


Route::middleware(['auth:api', 'email_verified'])->group(function () {

    //* User Auth
    Route::get('/auth', [UserAuthController::class, 'authUser'])
        ->name('auth');
    Route::post('/logout', [UserAuthController::class, 'logoutUser'])
        ->name('logout'); 
        
    //* User Account
    Route::post('/user-change-avatar', [UserAccountController::class, 'changeAvatar'])
        ->name('user.change.avatar');
    Route::post('/user-change-name', [UserAccountController::class, 'changeName'])
        ->name('user.change.name');
    Route::post('/user-change-password', [UserAccountController::class, 'changePassword'])
        ->name('user.change.password');
    
    //* Transfer Account Request 
    //* Email will be updated, after Emailverification, email_verified_at = null
    Route::post('/user-transfer-account', [TransferAccountController::class, 'sendToken'])
        ->name('user.transfer.account');

    //* Manage Profile
    Route::get('/load-user-avatar-profile', [UserProfileController::class, 'loadProfile'])
        ->name('load.user.avatar.profile');
    Route::post('/update-user-avatar-publicity', [UserProfileController::class, 'updatePublicity'])
        ->name('update.user.avatar.public.access');
    Route::post('/update-user-avatar-contact', [UserProfileController::class, 'updateContact'])
        ->name('update.user.avatar.contact');
    Route::post('/update-user-avatar-birth', [UserProfileController::class, 'updateBirth'])
        ->name('update.user.avatar.birth');
    Route::post('/update-user-avatar-location', [UserProfileController::class, 'updateLocation'])
        ->name('update.user.avatar.location');
    Route::post('/update-user-avatar-about', [UserProfileController::class, 'updateAbout'])
        ->name('update.user.avatar.about');
    Route::post('/update-user-avatar-country', [UserProfileController::class, 'updateCountry'])
        ->name('update.user.avatar.country');
    Route::post('/update-user-avatar-languages', [UserProfileController::class, 'updateLanguages'])
        ->name('update.user.avatar.languages');
    
    //* Delete User
    Route::post('/user-delete-account', [UserAccountController::class, 'deleteAccount'])
        ->name('user.delete.account');
});



//* -----------------------
//* Public Accessible
//* -----------------------

//* Login
Route::post('/login', [UserAuthController::class, 'loginUser'])
    ->middleware(['throttle:6,1'])    
    ->name('login');

//* Create Account
Route::post('/create-account', [CreateAccountController::class, 'register'])
    ->name('create.account');
Route::post('/email-verification-request', [EmailVerificationController::class, 'sendToken'])
    ->middleware(['throttle:5,1'])
    ->name('email.verification.request');
Route::put('/email-verification/{email}/{token}', [EmailVerificationController::class, 'verifyToken'])
    ->middleware(['throttle:5,1'])
    ->name('email.verification');

//* Reset Password
Route::post('/password-reset-request', [PasswordResetController::class, 'sendToken'])
    ->middleware(['throttle:6,1'])
    ->name('password.reset.request');
Route::put('/password-reset/{email}/{token}', [PasswordResetController::class, 'verifyToken'])
    ->middleware(['throttle:6,1'])
    ->name('password.reset');

//* Transfer Account
Route::put('/transfer-account/{email}/{token}/{transfer}', [TransferAccountController::class, 'verifyToken'])
    ->middleware(['throttle:6,1'])
    ->name('transfer.account');
