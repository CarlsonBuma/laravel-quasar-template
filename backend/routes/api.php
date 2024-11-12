<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Public\AttributesController;
use App\Http\Controllers\Public\AnalyticsAppController;
use App\Http\Controllers\Public\CommunityAvatarController;
use App\Http\Controllers\Public\CommunityEntityController;
use App\Http\Controllers\Public\CommunitySearchController;
use App\Http\Controllers\Public\CommunityCollaborationsController;


//* Analytics
Route::get('/analytics-get-public-stats', [AnalyticsAppController::class, 'getPublicStats'])
    ->name('analytics.get.public-stats');
Route::post('/analytics-post-app-visitor', [AnalyticsAppController::class, 'addAppVisitor'])
    ->name('analytics.post.app.visitor');

//* Get Attributes
Route::get('/load-app-awards', [AttributesController::class, 'getAwards'])
    ->name('load.app.language');
Route::get('/load-app-languages', [AttributesController::class, 'getLanguages'])
    ->name('load.app.language');
Route::get('/load-app-countries', [AttributesController::class, 'getCountries'])
    ->name('load.app.countries');
Route::get('/load-app-departements', [AttributesController::class, 'getDepartements'])
    ->name('load.app.departements');

//* Search Community
Route::get('/search-community-collaborations', [CommunityCollaborationsController::class, 'searchPublicCollaborations'])
    ->name('search.community.collaborations'); 
Route::get('/search-community-entities', [CommunitySearchController::class, 'searchPublicEntitites'])
    ->name('search.community.public.entities');
    
//* Public Profiles 
Route::get('/get-community-entity', [CommunityEntityController::class, 'getPublicEntity'])
    ->name('get.community.entity');
Route::get('/get-community-avatar', [CommunityAvatarController::class, 'getPublicAvatar'])
    ->name('get.community.avatar');
Route::get('/get-latest-public-entities', [CommunityEntityController::class, 'getLatestEntities'])
    ->name('get.latest.public.entities');

//* Import User Routes
if(env('ALLOW_USER_AUTH', false)) {
    require __DIR__.'/api/auth.php';
    require __DIR__.'/api/access.php';
    require __DIR__.'/api/admin.php';
    require __DIR__.'/api/user.php';
    require __DIR__.'/api/entity.php';
} 
    
