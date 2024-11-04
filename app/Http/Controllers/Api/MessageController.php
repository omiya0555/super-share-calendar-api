<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Message;
use App\Models\ChatRoom;
use App\Models\MessageFile;
use Illuminate\Support\Facades\Storage;
use App\Events\MessageSent;

class MessageController extends Controller
{
    // メッセージ一覧の取得
    public function index($chatRoomId)
    {
        $chatRoom = ChatRoom::find($chatRoomId);

        if (!$chatRoom) {
            return response()->json(['message' => 'チャットルームが見つかりません'], 404);
        }

        $messages = Message::where('chat_room_id', $chatRoomId)->with('files')->get();
        return response()->json($messages);
    }

    // メッセージの作成
    public function store(Request $request, $chatRoomId)
    {
        // チャットルームの存在確認とユーザーのロード
        $chatRoom = ChatRoom::with('users')->find($chatRoomId);
        if (!$chatRoom) {
            return response()->json(['message' => 'チャットルームが見つかりません'], 404);
        }
    
        $data = $request->validate([
            'content'   => 'required|string',
            'sender_id' => 'required|exists:users,id',
        ]);
    
        // sender_id がチャットルームに所属しているかチェック
        $isMember = $chatRoom->users->contains('id', $data['sender_id']);
        if (!$isMember) {
            return response()->json(['message' => 'このユーザーはチャットルームに所属していません'], 403);
        }
    
        // メッセージ作成
        $message = Message::create([
            'chat_room_id' => $chatRoomId,
            'sender_id'    => $data['sender_id'],
            'content'      => $data['content'],
        ]);

        // メッセージ送信イベントを発火
        event(new MessageSent($message));
    
        return response()->json($message, 201);
    }

    // メッセージの削除
    public function destroy($chatRoomId, $messageId)
    {
        $message = Message::where('id', $messageId)->where('chat_room_id', $chatRoomId)->first();

        if (!$message) {
            return response()->json(['message' => 'メッセージが見つかりません'], 404);
        }

        $message->delete();

        return response()->json(['message' => 'メッセージを削除しました']);
    }

    // メッセージへのファイル添付
    public function attachFile(Request $request, $messageId)
    {
        $message = Message::find($messageId);

        if (!$message) {
            return response()->json(['message' => 'メッセージが見つかりません'], 404);
        }

        $request->validate([
            'file' => 'required|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:2048',
        ]);

        $file = $request->file('file');
        $filePath = $file->store('message_files', 'public');
        $messageFile = MessageFile::create([
            'message_id'    => $message->id,
            'file_url'      => Storage::url($filePath),
            'uploaded_by'   => $request->user()->id,
        ]);

        return response()->json($messageFile, 201);
    }
}
