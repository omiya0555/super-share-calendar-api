<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use App\Models\User;
use App\Http\Controllers\Controller;


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
            
            throw ValidationException::withMessages([
                'email' => ['誤ったクレデンシャル情報です。'],
            ]);

            $user = Auth::user();
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'message' => 'ログイン成功',
                'token' => $token,
                'user' => $user
            ], 200);
        }

        return response()->json(['message' => '認証に失敗しました'], 401);
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