<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use App\Events\NotificationSent;

class NotificationController extends Controller
{
    // 通知一覧の取得
    public function index()
    {
        $userId = Auth::id();
        $notifications = Notification::where('user_id', $userId)->orderBy('created_at', 'desc')->get();

        return response()->json($notifications, 200);
    }

    // 通知の詳細表示
    public function show($id)
    {
        $userId = Auth::id();
        $notification = Notification::where('id', $id)->where('user_id', $userId)->first();

        if (!$notification) {
            return response()->json(['message' => '通知が見つかりません'], 404);
        }

        return response()->json($notification, 200);
    }

    // 通知の作成
    public function store(Request $request)
    {
        $data = $request->validate([
            'user_id'     => 'required|exists:users,id',
            'title'       => 'required|string|max:255',
            'content'     => 'required|string',
            'type'        => 'required|string|in:group,individual,global',
        ]);

        try {
            DB::beginTransaction();

            $notification = Notification::create([
                'user_id' => $data['user_id'],
                'title'   => $data['title'],
                'content' => $data['content'],
                'type'    => $data['type'],         // type で グループ、個人、全体を判別
            ]);

            // イベント発火 typeごとに異なるチャネルを利用
            event(new NotificationSent($notification));

            DB::commit();
            return response()->json(['message' => '通知が作成されました', 'notification' => $notification], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => '通知の作成に失敗しました', 'error' => $e->getMessage()], 500);
        }
    }

    // 通知の既読処理
    public function markAsRead($id)
    {
        $userId = Auth::id();
        $notification = Notification::where('id', $id)->where('user_id', $userId)->first();

        if (!$notification) {
            return response()->json(['message' => '通知が見つかりません'], 404);
        }

        if ($notification->read_at) {
            return response()->json(['message' => '通知はすでに既読です'], 200);
        }

        $notification->update(['read_at' => Carbon::now()]);
        return response()->json(['message' => '通知を既読にしました']);
    }
}
