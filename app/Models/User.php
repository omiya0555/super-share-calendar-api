<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'user_icon_id',
    ];

    // イベントリレーション
    public function events()
    {
        return $this->hasMany(Event::class, 'organizer_id');
    }

    // コメントリレーション
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    // 通知リレーション
    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    // 参加者リレーション
    public function participatingEvents()
    {
        return $this->belongsToMany(Event::class, 'event_participants')->withTimestamps()->withPivot('status', 'viewed');
    }

    // チャットルームリレーション
    public function chatRooms()
    {
        return $this->belongsToMany(ChatRoom::class, 'chat_room_users')->withTimestamps();
    }
    
    // アイコンリレーション
    public function icon()
    {
        return $this->belongsTo(UserIcon::class, 'user_icon_id');
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];
}
