<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserEntity\EntityController;
use App\Http\Controllers\UserEntity\EntityProfileController;
use App\Http\Controllers\UserEntity\EntityCollaboratorsController;
use App\Http\Controllers\UserEntity\EntityCollaborationController;


Route::middleware(['auth:api', 'email_verified', 'access_businesscockpit'])->group(function () {

    //* Entity
    Route::get('/load-entity-dashboard', [EntityController::class, 'loadDashboard'])
        ->name('load.entity.dashboard');
        
    //* Entity Collaborations
    Route::get('/load-entity-awards/{all?}', [EntityCollaborationController::class, 'loadAwards'])
        ->name('get.entity.awards');
    Route::post('/create-entity-collaboration', [EntityCollaborationController::class, 'createCollaboration'])
        ->name('create.entity.collaboration');
    Route::post('/make-entity-collaboration-public', [EntityCollaborationController::class, 'updatePublicity'])
        ->name('make.entity.collaboration.public'); 
    Route::post('/update-entity-collaboration-limit', [EntityCollaborationController::class, 'updateLimit'])
        ->name('update.entity.collaboration.limit'); 
    Route::post('/update-entity-collaboration-tags', [EntityCollaborationController::class, 'updateTags'])
        ->name('update.entity.collaboration.tags'); 
    Route::post('/archive-entity-collaboration', [EntityCollaborationController::class, 'archiveCollaboration'])
        ->name('archive.entity.collaboration'); 
    Route::post('/unarchive-entity-collaboration', [EntityCollaborationController::class, 'unarchiveCollaboration'])
        ->name('unarchive.entity.collaboration'); 
    Route::post('/delete-entity-collaboration', [EntityCollaborationController::class, 'deleteCollaboration'])
        ->name('delete.entity.collaboration'); 

    //* Entity Collaborators
    Route::get('/get-active-reward-collaborations', [EntityCollaboratorsController::class, 'loadActiveRewardCollaborators'])
        ->name('get.active.reward.collaborations');
    Route::get('/get-closed-reward-collaborations', [EntityCollaboratorsController::class, 'loadClosedRewardCollaborators'])
        ->name('get.closed.reward.collaborations'); 
    Route::get('/search-new-collaborator', [EntityCollaboratorsController::class, 'searchNewCollaborator'])
        ->name('search.new.collaborator');
    Route::post('/release-collaboration-to-new-collaborator', [EntityCollaboratorsController::class, 'releaseCollaborationToNewCollaborator'])
        ->name('release.collaboration.to.new.collaborator');
    Route::post('/release-collaboration-to-collaborator', [EntityCollaboratorsController::class, 'releaseCollaborationToCollaborator'])
        ->name('release.collaboration.to.collaborator');
    Route::post('/issue-collaboration-to-collaborator', [EntityCollaboratorsController::class, 'issueCollaborationToCollaborator'])
        ->name('issue.collaboration.to.collaborator');
    Route::post('/reopen-collaboration-collaborator', [EntityCollaboratorsController::class, 'reopenCollaborator'])
        ->name('reopen.collaboration.collaborator');
        
    Route::post('/delete-collaboration-collaborator', [EntityCollaboratorsController::class, 'deleteCollaborator'])
        ->name('delete.collaboration.collaborator');
    
    //* Entity Profile
    Route::get('/load-entity-profile', [EntityProfileController::class, 'loadEntityProfile'])
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
    Route::post('/update-entity-bulletpoints', [EntityProfileController::class, 'updateBulletpoints'])
        ->name('update.entity.bulletpoints');
    Route::post('/update-entity-location', [EntityProfileController::class, 'updateLocation'])
        ->name('update.entity.location');
});
