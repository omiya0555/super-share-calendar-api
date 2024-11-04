<?php

use Illuminate\Support\Facades\Broadcast;

// 個人チャットチャネル
Broadcast::channel('chat-room.{chatRoomId}', function ($user, $chatRoomId) {
    return $user->chatRooms()->where('chat_room_id', $chatRoomId)->exists();
});

// グループチャットチャネル
Broadcast::channel('group-chat.{chatRoomId}', function ($user, $chatRoomId) {
    return $user->chatRooms()->where('chat_room_id', $chatRoomId)->where('is_group', true)->exists();
});

// ユーザー個別通知チャネル
Broadcast::channel('user.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// パブリックチャネル
Broadcast::channel('public-channel', function () {
    return true;
});