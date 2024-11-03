<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;

    protected $fillable = [
        'chat_room_id',
        'user_id',
        'content'
    ];

    // チャットルームリレーション
    public function chatRoom()
    {
        return $this->belongsTo(ChatRoom::class);
    }

    // ユーザーリレーション
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // ファイルリレーション
    public function files()
    {
        return $this->hasMany(MessageFile::class);
    }

    // 送信者とのリレーション
    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }
}

