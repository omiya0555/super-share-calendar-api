<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'all_day', "color", 'start_time', 'end_time', 'organizer_id'];

    // ユーザーリレーション
    public function organizer()
    {
        return $this->belongsTo(User::class, 'organizer_id');
    }

    // コメントリレーション
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    // UserとEventの多対多リレーション
    // pivot プロパティを通じて event_participant にアクセス
    public function participants()
    {
        return $this->belongsToMany(User::class, 'event_participants')->withTimestamps()->withPivot('status', 'viewed');
    }
}
