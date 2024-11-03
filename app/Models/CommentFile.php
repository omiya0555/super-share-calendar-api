<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommentFile extends Model
{
    use HasFactory;

    protected $fillable = [
        'comment_id', 
        'file_url', 
        'uploaded_by'
    ];

    // コメントリレーション
    public function comment()
    {
        return $this->belongsTo(Comment::class);
    }

    public function uploadedBy()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
