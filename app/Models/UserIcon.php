<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserIcon extends Model
{
    use HasFactory;

    protected $fillable = ['icon_url'];

    // ユーザーリレーション
    public function user()
    {
        return $this->hasOne(User::class);
    }
}