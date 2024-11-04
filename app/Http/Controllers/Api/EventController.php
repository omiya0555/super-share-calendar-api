<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Event;
use App\Models\User;
use App\Models\ChatRoom;
use App\Models\EventParticipant;

class EventController extends Controller
{
    // イベント一覧及び参加者一覧の取得
    public function index()
    {
        $events = Event::with(['participants'])->get();
        return response()->json($events);
    }

    // イベントの詳細表示
    public function show($id)
    {
        $event = Event::with(['participants'])->find($id);

        if (!$event) {
            return response()->json(['message' => 'イベントが見つかりません'], 404);
        }

        return response()->json($event);
    }

    // イベントの作成（参加者追加含む）
    public function store(Request $request)
    {
        $data = $request->validate([
            'title'                 => 'required|string|max:255',
            'start_time'            => 'required|date',
            'end_time'              => 'required|date|after_or_equal:start',
            'all_day'               => 'boolean',
            'color'                 => 'nullable|string',
            'location'              => 'nullable|string',
            'participants'          => 'sometimes|array|min:1',
            'participants.*.user_id'=> 'exists:users,id',
        ]);

        $data['organizer_id'] = $request->user()->id;

        DB::beginTransaction();

        try {
            $event = Event::create($data);

            // syncParticipantsメソッドを用いて参加者を追加
            if (!empty($data['participants'])) {
                $this->syncParticipants($event, $data['participants']);
            }

            // イベント専用のグループチャットルームを作成
            $chatRoom = ChatRoom::create([
                'room_name' => $event->title . '-Group', // 末尾にグループを表す文字を追加
                'is_group'  => true,
            ]);

            // 参加者全員をチャットルームに追加
            foreach ($event->participants as $participant) {
                $chatRoom->users()->attach($participant->id);
            }

            // イベント情報に作成したグループチャットルームのIDを追加
            $event->update(['chat_room_id' => $chatRoom->id]);

            DB::commit();
            return response()->json(['message' => 'イベント・チャットルーム作成に成功しました。', 'event' => $event], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'イベント・チャットルーム作成に失敗しました。', 'error' => $e->getMessage()], 500);
        }
    }

    // イベントの更新（participants 更新含む）
    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'title'                 => 'string|max:255',
            'start_time'            => 'date',
            'end_time'              => 'date|after_or_equal:start',
            'all_day'               => 'boolean',
            'color'                 => 'nullable|string',
            'location'              => 'nullable|string',
            'participants'          => 'sometimes|array|min:1',
            'participants.*.user_id'=> 'exists:users,id',
        ]);

        $event = Event::find($id);
        if (!$event) {
            return response()->json(['message' => 'イベントが見つかりません'], 404);
        }

        // organizer_id が現在のログインユーザーと一致するか確認（権限チェック）
        if ($event->organizer_id !== $request->user()->id) {
            return response()->json(['message' => '権限がありません'], 403);
        }

        DB::beginTransaction();

        try {
            $event->update($data);

            if (isset($data['participants'])) {
                $this->syncParticipants($event, $data['participants']);
            }

            DB::commit();
            return response()->json(['message' => 'イベント更新成功', 'event' => $event], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'イベント更新失敗', 'error' => $e->getMessage()], 500);
        }
    }

    // イベントの削除
    public function destroy($id)
    {
        $event = Event::find($id);
        if (!$event) {
            return response()->json(['message' => 'イベントが見つかりません'], 404);
        }

        if ($event->organizer_id !== request()->user()->id) {
            return response()->json(['message' => '権限がありません'], 403);
        }

        $event->delete();
        return response()->json(['message' => 'イベントを削除しました']);
    }

    public function addParticipant(Request $request, $eventId)
    {
        $event = Event::find($eventId);
        if (!$event) {
            return response()->json(['message' => 'イベントが見つかりません'], 404);
        }
    
        $userId = $request->input('user_id');
        $user = User::find($userId);
        if (!$user) {
            return response()->json(['message' => 'ユーザーが見つかりません'], 404);
        }
    
        // 主催者または既存参加者のチェック
        if ($event->organizer_id == $userId) {
            return response()->json(['message' => '主催者は既に参加者です'], 409);
        }
        if ($event->participants()->where('user_id', $userId)->exists()) {
            return response()->json(['message' => 'ユーザーは既に参加者として追加されています'], 409);
        }
    
        // 参加者の追加
        $event->participants()->attach($userId);
        return response()->json(['message' => '参加者を追加しました']);
    }

    // 参加者の削除
    public function removeParticipant(Request $request, $eventId)
    {
        $event = Event::find($eventId);
        if (!$event) {
            return response()->json(['message' => 'イベントが見つかりません'], 404);
        }
    
        $userId = $request->input('user_id');
    
        // 該当イベントの参加者チェック
        if (!$event->participants()->where('user_id', $userId)->exists()) {
            return response()->json(['message' => 'ユーザーは参加者ではありません'], 404);
        }

        // 主催者を削除しようとした場合
        if ($userId == $event->organizer_id) {
            return response()->json(['message' => '主催者は削除できません'], 400);
        }
    
        // 参加者の削除
        $event->participants()->detach($userId);
        return response()->json(['message' => '参加者を削除しました']);
    }

    // 参加状況の更新
    public function updateParticipantStatus(Request $request, $eventId, $userId)
    {
        $data = $request->validate([
            'status' => 'required|boolean',
            'viewed' => 'required|boolean',
        ]);

        $eventParticipant = EventParticipant::where('event_id', $eventId)
            ->where('user_id', $userId)
            ->first();

        if (!$eventParticipant) {
            return response()->json(['message' => '参加者が見つかりません'], 404);
        }

        if ($event->organizer_id !== request()->user()->id) {
            return response()->json(['message' => '権限がありません'], 403);
        }

        try {
            $eventParticipant->update($data);
            return response()->json(['message' => '参加状況を更新しました'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => '参加状況の更新に失敗しました', 'error' => $e->getMessage()], 500);
        }
    }

    // 参加者の同期処理（追加・削除の簡略化）
    private function syncParticipants($event, $participants)
    {
        // 現在の参加者のユーザーIDを抽出
        $existingParticipants = $event->participants()->pluck('user_id')->toArray();
        // 新しい参加者のIDのみを格納
        $newParticipants = array_column($participants, 'user_id');

        // 主催者IDを除外し、メッセージを返す
        if (in_array($event->organizer_id, $newParticipants)) {
            return response()->json(['message' => '主催者はすでに参加者として追加されています'], 409);
        }

        // すでに参加しているユーザーのIDをチェック
        // 新しい参加者ー現在の参加者＝追加すべき参加者
        $participantsToAdd = array_diff($newParticipants, $existingParticipants);
        if (empty($participantsToAdd)) {
            return response()->json(['message' => '指定されたユーザーはすでに参加者として追加されています'], 409);
        }

        // 現在の参加者ー新しい参加者＝削除すべき参加者
        $participantsToRemove = array_diff($existingParticipants, $newParticipants);

        // 参加者の削除
        if (!empty($participantsToRemove)) {
            $event->participants()->detach($participantsToRemove);
        }

        // 新しい参加者の追加
        foreach ($participantsToAdd as $userId) {
            $event->participants()->attach($userId);
        }
    }
}