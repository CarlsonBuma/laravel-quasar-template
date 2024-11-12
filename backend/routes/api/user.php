<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\User\UserShortcutsController;
use App\Http\Controllers\User\UserCollaboratorController;
use App\Http\Controllers\User\UserDashboardController;

// ********************************
//* User Default
// ********************************
Route::middleware(['auth:api', 'email_verified'])->group(function () {

    //* User Collaborations
    Route::get('/load-user-dashboard', [UserDashboardController::class, 'loadDashboard'])
        ->name('load.user.active.collaborations');

    //* Uster Collaboration Flags
    Route::get('/get-user-released-collaborations', [UserCollaboratorController::class, 'loadReleasedRewards'])
        ->name('get.user.released.collaborations');
    Route::post('/user-agree-collaboration-request', [UserCollaboratorController::class, 'agreeRequestedCollaboration'])
        ->name('user.agree.collaboration.request');
    Route::post('/user-request-collaboration', [UserCollaboratorController::class, 'requestNewCollaboration'])
        ->name('user.request.collaboration');
    Route::post('/user-confirm-collaboration', [UserCollaboratorController::class, 'confirmCollaboration'])
        ->name('user.confirm.collaboration');
    Route::post('/user-publish-collaboration', [UserCollaboratorController::class, 'publishCollaboration'])
        ->name('user.publish.collaboration');
    Route::post('/user-update-collaboration-start', [UserCollaboratorController::class, 'updateStart'])
        ->name('user.update.collaboration.start');
    Route::post('/user-update-collaboration-duration', [UserCollaboratorController::class, 'updateDuration'])
        ->name('user.update.collaboration.duration');
    Route::post('/user-remove-collaboration', [UserCollaboratorController::class, 'removeCollaboration'])
        ->name('user.remove.collaboration');

    //* Manage Shortcuts
    Route::get('/get-user-entities-shortcuts', [UserShortcutsController::class, 'getShortcutEntities'])
        ->name('get.user.entities.shortcuts');
    Route::post('/add-user-entity-shortcut', [UserShortcutsController::class, 'connectUserWithEntity'])
        ->name('add.user.entity.shortcut');
    Route::delete('/remove-user-entity-shortcut/{entity_id}', [UserShortcutsController::class, 'removeUserFromEntity'])
        ->name('remove.user.entity.shortcut');
});
