<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ChatRoom;
use App\Models\ChatRoomUser;
use App\Models\Message;
use App\Models\User;

class ChatRoomController extends Controller
{
    // チャットルーム一覧の取得
    public function index(Request $request)
    {
        $chatRooms = ChatRoom::whereHas('users', function ($query) use ($request) {
            $query->where('user_id', $request->user()->id);
        })->with('users')->get();

        return response()->json($chatRooms);
    }

    // チャットルームの作成
    public function store(Request $request)
    {
        $data = $request->validate([
            'room_name'         => 'required_if:is_group,true|string|max:255', // グループなら部屋名は必須
            'is_group'          => 'required|boolean',
            'users'             => 'required|array|min:1',
            'users.*.user_id'   => 'exists:users,id',
        ]);
    
        if ($data['is_group']) {
            // グループチャットの場合
            $chatRoom = ChatRoom::create([
                'room_name' => $data['room_name'],
                'is_group' => true
            ]);
    
            foreach ($data['users'] as $user) {
                ChatRoomUser::create(['chat_room_id' => $chatRoom->id, 'user_id' => $user['user_id']]);
            }
    
            return response()->json(['message' => 'グループチャットルームを作成しました', 'chatRoom' => $chatRoom], 201);
    
        } else {
            // 個人チャットの場合
            if (count($data['users']) !== 2) {
                return response()->json(['message' => '個人チャットには2人のユーザーが必要です'], 400);
            }
    
            $userIds = array_column($data['users'], 'user_id');
            sort($userIds);
    
            // 同じ組み合わせの個人チャットがあるか確認
            $existingRoom = ChatRoom::where('is_group', false)
            ->whereHas('users', function ($query) use ($userIds) {
                $query->select('chat_room_id')
                      ->whereIn('user_id', $userIds)
                      ->groupBy('chat_room_id')
                      ->havingRaw('COUNT(DISTINCT user_id) = ?', [2]);
            })
            ->first();
            if ($existingRoom) {
                return response()->json(['message' => 'すでに個人チャットルームが存在しています'], 409);
            }
    
            // 新規個人チャットルーム作成
            $chatRoom = ChatRoom::create([
                'room_name' => $data['room_name'] ?? null,  // 個人チャットではroom_nameを省略可能
                'is_group'  => false
            ]);
    
            foreach ($data['users'] as $user) {
                ChatRoomUser::create(['chat_room_id' => $chatRoom->id, 'user_id' => $user['user_id']]);
            }
    
            return response()->json(['message' => '個人チャットルームを作成しました', 'chatRoom' => $chatRoom], 201);
        }
    }

    // チャットルームへのユーザー追加
    public function addUser(Request $request, $chatRoomId)
    {
        $chatRoom = ChatRoom::find($chatRoomId);

        if (!$chatRoom) {
            return response()->json(['message' => 'チャットルームが見つかりません'], 404);
        }
        // is_groupフラグで個人チャットへの追加を拒否
        if (!$chatRoom->is_group) {
            return response()->json(['message' => '個人チャットにユーザーの追加はできません。'], 400);
        }

        $userId = $request->input('user_id');
        $user = User::find($userId);
        if (!$user) {
            return response()->json(['message' => 'ユーザーが見つかりません'], 404);
        }

        if ($chatRoom->users()->where('user_id', $userId)->exists()) {
            return response()->json(['message' => 'ユーザーはすでにチャットルームに追加されています'], 409);
        }

        $chatRoom->users()->attach($userId);
        return response()->json(['message' => 'ユーザーをチャットルームに追加しました']);
    }

    // チャットルームの削除
    public function destroy($chatRoomId)
    {
        $chatRoom = ChatRoom::find($chatRoomId);
        if (!$chatRoom) {
            return response()->json(['message' => 'チャットルームが見つかりません'], 404);
        }

        // 関連するメッセージと参加情報を削除
        $chatRoom->messages()->delete();
        $chatRoom->users()->detach();
        $chatRoom->delete();

        return response()->json(['message' => 'チャットルームを削除しました']);
    }

    // ChatRoomIdを取得、又は作成及び取得
    // 個人チャットルーム遷移時に活用
    public function getOrCreateChatRoom(Request $request)
    {
        $data = $request->validate([
            'user_id'      => 'required|exists:users,id',       // 自分のID
            'partner_id'   => 'required|exists:users,id',       // 相手のID
        ]);
    
        // 個人チャットルームの存在を確認
        $userIds = [$data['user_id'], $data['partner_id']];
        sort($userIds);
    
        // 個人チャットの重複確認
        $existingRoom = ChatRoom::where('is_group', false)
            ->whereHas('users', function ($query) use ($userIds) {
                $query->select('chat_room_id')
                      ->whereIn('user_id', $userIds)
                      ->groupBy('chat_room_id')
                      ->havingRaw('COUNT(DISTINCT user_id) = ?', [2]);
            })
            ->first();
    
        if ($existingRoom) {
            return response()->json(['chat_room_id' => $existingRoom->id]);
        }
    
        // 新規個人チャットルーム作成
        $chatRoom = ChatRoom::create([
            'room_name' => null,
            'is_group' => false,
        ]);
    
        // ユーザーと相手を追加
        ChatRoomUser::create(['chat_room_id' => $chatRoom->id, 'user_id' => $data['user_id']]);
        ChatRoomUser::create(['chat_room_id' => $chatRoom->id, 'user_id' => $data['partner_id']]);
    
        return response()->json(['chat_room_id' => $chatRoom->id]);
    }
}