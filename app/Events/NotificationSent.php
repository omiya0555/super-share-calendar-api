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

        } elseif ($this->notification->type === 'group') {
            return new PrivateChannel('group-chat.' . $this->notification->group_id);
            
        } else {
            return new Channel('public-channel');
        }
    }
}
