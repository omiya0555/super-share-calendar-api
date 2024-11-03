<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MessageFile extends Model
{
    use HasFactory;

    protected $fillable = [
        'message_id', 
        'file_url', 
        'uploaded_by'
    ];

    // メッセージリレーション
    public function message()
    {
        return $this->belongsTo(Message::class);
    }

    public function uploadedBy()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
