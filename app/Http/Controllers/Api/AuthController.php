<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Validation\ValidationException;
use App\Models\User;


class AuthController extends Controller
{
    // ログイン処理
    public function login(Request $request)
    {
        // バリデーション
        $validated = $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        // 認証チェック
        if (Auth::attempt($validated)) {
            $user = Auth::user();
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'message' => 'ログイン成功',
                'token' => $token,
                'user' => $user
            ], 200);
        }

        // 認証失敗時のエラーメッセージ
        throw ValidationException::withMessages([
            'email' => ['誤ったクレデンシャル情報です。'],
        ]);
    }

    // ログアウト処理
    public function logout(Request $request)
    {
        // 現在のトークンを削除
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'ログアウトしました'], 200);
    }

    // ログイン情報の取得
    public function user(Request $request)
    {
        return response()->json($request->user());
    }
}