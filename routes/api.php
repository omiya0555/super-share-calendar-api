<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;

Route::post('/login', [AuthController::class, 'login']);


// 認証が必要なルート群
Route::middleware('auth:sanctum')->group(function () {

    // AuthController: 認証関連のルートs
    Route::post('/logout',      [AuthController::class, 'logout']);
    Route::get( '/user',        [AuthController::class, 'user']);
    
    // UserController: ユーザー関連のルート
    Route::get( '/users',       [UserController::class, 'index']);
    Route::get( '/users/{id}',  [UserController::class, 'show']);
});

