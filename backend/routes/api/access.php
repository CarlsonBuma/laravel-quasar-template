<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Access\UserAccessController;
use App\Http\Controllers\Access\UserTransactionController;
use App\Http\Controllers\Access\UserSubscriptionController;


// ********************************
//* Access Management by Paddle
// ********************************
Route::middleware(['auth:api', 'email_verified'])->group(function () {
    
    // Check access
    Route::get('/user-check-access/{access_token}', [UserAccessController::class, 'checkUserAccess'])
        ->name('user.check.access');
    Route::get('/user-load-access', [UserAccessController::class, 'loadUserAccess'])
        ->name('user.load.access');

    // Manage Transaction
    Route::post('/user-set-client-access', [UserTransactionController::class, 'initializeClientCheckout'])
        ->name('user.set.client.access');
    Route::post('/user-verify-client-access', [UserTransactionController::class, 'verifyClientCheckout'])
        ->name('user.verifiy.client.access');
        
    // Manage subscriptions
    Route::post('/user-cancel-subscription', [UserSubscriptionController::class, 'cancelSubscription'])
        ->name('user.cancel.subscription');
});
