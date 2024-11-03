<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;

class UserController extends Controller
{
    // ユーザー一覧及びアイコン情報の取得
    public function index()
    {
        $users = User::with('icon')->get();
        return response()->json($users);
    }

    // ユーザー詳細の取得
    public function show($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'ユーザーが見つかりません'], 404);
        }

        return response()->json($user);
    }
}

