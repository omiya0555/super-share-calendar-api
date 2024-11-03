<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\EventController;
use App\Http\Controllers\Api\CommentController;

Route::post('/login', [AuthController::class, 'login']);                        // ログイン処理-トークン発行


// 認証が必要なルート群
Route::middleware('auth:sanctum')->group(function () {

    // AuthController: 認証関連のルートs
    Route::post('/logout',      [AuthController::class, 'logout']);             // ログアウト処理
    Route::get( '/user',        [AuthController::class, 'user']);               // ログインユーザー情報取得
    
    // UserController: ユーザー関連のルート
    Route::get( '/users',       [UserController::class, 'index']);              // ユーザー一覧取得
    Route::get( '/users/{id}',  [UserController::class, 'show']);               // ユーザー詳細取得

    // EventController: イベント関連ルート
    Route::prefix('events')->group(function () {
        Route::get('/',         [EventController::class, 'index']);             // イベント一覧取得
        Route::get('/{id}',     [EventController::class, 'show']);              // イベント詳細取得
        Route::post('/',        [EventController::class, 'store']);             // イベント作成
        Route::put('/{id}',     [EventController::class, 'update']);            // イベント更新
        Route::delete('/{id}',  [EventController::class, 'destroy']);           // イベント削除

        // それぞれ参加者の追加、削除、状態更新
        Route::post('/{eventId}/participants',  [EventController::class, 'addParticipant']);
        Route::delete('/{eventId}/participants',[EventController::class, 'removeParticipant']);
        Route::patch('/{eventId}/participants/{userId}', [EventController::class, 'updateParticipantStatus']);
    });

    // CommentController: コメント関連ルート
    Route::prefix('events/{eventId}/comments')->group(function () {
        Route::get('/', [CommentController::class, 'index']);                   // コメント一覧取得
        Route::post('/', [CommentController::class, 'store']);                  // コメント作成
        Route::put('/{commentId}', [CommentController::class, 'update']);              // コメント更新
        Route::delete('/{commentId}', [CommentController::class, 'destroy']);          // コメント削除
        Route::post('/{commentId}/files', [CommentController::class, 'attachFile']);   // コメントへのファイル添付
    });

});

