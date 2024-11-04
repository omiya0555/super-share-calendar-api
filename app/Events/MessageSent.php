<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\Message;

class MessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;

    public function __construct(Message $message)
    {
        $this->message = $message;
    }

    public function broadcastOn()
    {
        if ($this->message->chatRoom->is_group) {
            return new PrivateChannel('group-chat.' . $this->message->chat_room_id);
        } else {
            return new PrivateChannel('chat-room.' . $this->message->chat_room_id);
        }
    }

    public function broadcastAs()
    {
        return 'message.sent';
    }
}