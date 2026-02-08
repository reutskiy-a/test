<?php

use App\Modules\Tz\Controllers\CompanyPlacementController;
use App\Modules\Tz\Controllers\EventUpdateCompanyController;
use App\Modules\Tz\Controllers\InstallationController;
use App\Modules\Tz\Controllers\AddDataController;
use App\Modules\Tz\Controllers\UserFieldController;
use Illuminate\Support\Facades\Route;

Route::any('/install', [InstallationController::class, 'install']);
Route::any('/install/create-table', [InstallationController::class, 'createTable']);



Route::prefix('tz')->group(function() {

    Route::prefix('add-data')->group(function() {
        Route::post('/', [AddDataController::class, 'index']);
        Route::any('/test', [AddDataController::class, 'test']);
    });

    Route::prefix('event')->group(function() {
        Route::get('/', [EventUpdateCompanyController::class, 'index']);
        Route::get('/get-event-list', [EventUpdateCompanyController::class, 'getEventList']);
        Route::delete('/delete-event', [EventUpdateCompanyController::class, 'deleteEvent']);
        Route::post('/add-update-event', [EventUpdateCompanyController::class, 'addUpdateEvent']);
        Route::post('/handle-on-update', [EventUpdateCompanyController::class, 'handleOnUpdateEvent']);
    });

    Route::prefix('placement')->group(function() {
        Route::post('/add', [CompanyPlacementController::class, 'addPlacement']);
        Route::any('/list', [CompanyPlacementController::class, 'getPlacementList']);
        Route::delete('/delete', [CompanyPlacementController::class, 'deletePlacement']);
        Route::any('/handle', [CompanyPlacementController::class, 'placementHandler']);
        Route::get('/get-deals', [CompanyPlacementController::class, 'getCompanyDeals']);
    });

    Route::prefix('userfield')->group(function() {
        Route::any('/', [UserFieldController::class, 'index']);
        Route::any('/handle', [UserFieldController::class, 'handle']);
        Route::delete('/delete', [UserFieldController::class, 'delete']);
    });

});

Route::any('get-progress', [AddDataController::class, 'getProgress']);
