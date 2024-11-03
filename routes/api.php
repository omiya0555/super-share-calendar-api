<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\EventController;

Route::post('/login', [AuthController::class, 'login']);


// 認証が必要なルート群
Route::middleware('auth:sanctum')->group(function () {

    // AuthController: 認証関連のルートs
    Route::post('/logout',      [AuthController::class, 'logout']);
    Route::get( '/user',        [AuthController::class, 'user']);
    
    // UserController: ユーザー関連のルート
    Route::get( '/users',       [UserController::class, 'index']);
    Route::get( '/users/{id}',  [UserController::class, 'show']);

    // EventController: イベント関連ルート
    Route::prefix('events')->group(function () {
        Route::get('/',         [EventController::class, 'index']);
        Route::get('/{id}',     [EventController::class, 'show']);
        Route::post('/',        [EventController::class, 'store']);
        Route::put('/{id}',     [EventController::class, 'update']);
        Route::delete('/{id}',  [EventController::class, 'destroy']);
        Route::post('/{eventId}/participants',  [EventController::class, 'addParticipant']);
        Route::delete('/{eventId}/participants',[EventController::class, 'removeParticipant']);
        Route::patch('/{eventId}/participants/{userId}', [EventController::class, 'updateParticipantStatus']);
    });

});

