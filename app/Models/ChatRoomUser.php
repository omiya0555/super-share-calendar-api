<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Pivot;

class ChatRoomUser extends Pivot
{
    protected $table = 'chat_room_users';

    protected $fillable = [
        'chat_room_id',
        'user_id',
    ];

    public $timestamps = true; // 作成日時や更新日時を記録
}