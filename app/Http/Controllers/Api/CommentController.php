<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Event;
use App\Models\Comment;
use App\Models\CommentFile;
use Illuminate\Support\Facades\Storage;

class CommentController extends Controller
{
    // コメントの一覧取得
    public function index($eventId)
    {
        $event = Event::find($eventId);

        if (!$event) {
            return response()->json(['message' => 'イベントが見つかりません'], 404);
        }

        $comments = Comment::with('files')->where('event_id', $eventId)->get();
        return response()->json($comments);
    }

    // コメントの作成
    public function store(Request $request, $eventId)
    {
        $event = Event::find($eventId);

        if (!$event) {
            return response()->json(['message' => 'イベントが見つかりません'], 404);
        }
        
        $data = $request->validate([
            'content' => 'required|string',
            'user_id' => 'required|exists:users,id',
        ]);

        $comment = Comment::create(array_merge($data, ['event_id' => $eventId]));

        return response()->json($comment, 201);
    }

    // コメントの削除
    public function destroy($eventId, $commentId )             
    {
        $comment = Comment::where('id', $commentId)->where('event_id', $eventId)->first();

        if (!$comment) {
            return response()->json(['message' => 'コメントが見つかりません'], 404);
        }

        $comment->delete();

        return response()->json(['message' => 'コメントを削除しました']);
    }

    // コメントの編集
    public function update(Request $request, $eventId, $commentId )
    {
        $comment = Comment::where('id', $commentId)->where('event_id', $eventId)->first();

        if (!$comment) {
            return response()->json(['message' => 'コメントが見つかりません'], 404);
        }

        $data = $request->validate([
            'content' => 'string',
        ]);

        $comment->update($data);

        return response()->json($comment);
    }

    // コメントへのファイル添付

    // ！！！　未テスト　！！！
    
    public function attachFile(Request $request, $commentId)
    {
        $comment = Comment::find($commentId);

        if (!$comment) {
            return response()->json(['message' => 'コメントが見つかりません'], 404);
        }

        $request->validate([
            'file' => 'required|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:2048',
        ]);

        $file = $request->file('file');
        $filePath = $file->store('comment_files', 'public');
        $commentFile = CommentFile::create([
            'comment_id'    => $comment->id,
            'file_url'      => Storage::url($filePath),
            'uploaded_by'   => $request->user()->id,
        ]);

        return response()->json($commentFile, 201);
    }
}