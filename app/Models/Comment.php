<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'user_id',
        'content'
    ];

    // イベントリレーション
    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    // ユーザーリレーション
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // ファイルリレーション
    public function files()
    {
        return $this->hasMany(CommentFile::class);
    }
}
