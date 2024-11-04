<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\Notification;

class NotificationSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $notification;

    public function __construct(Notification $notification)
    {
        $this->notification = $notification;
    }

    public function broadcastOn()
    {
        // 個別通知やグループ通知の条件でチャネルを分ける
        if ($this->notification->type === 'individual') {
            return new PrivateChannel('user.' . $this->notification->user_id);

        } elseif ($this->notification->type === 'group' && $this->notification->chat_room_id) {
            // chat_room_id を用いてグループを特定し、チャネルを設定
            return new PrivateChannel('group-chat.' . $this->notification->chat_room_id);

        } else {
            return new Channel('public-channel');
        }
    }
}
