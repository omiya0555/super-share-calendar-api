<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Pivot;

class EventParticipant extends Pivot
{
    protected $table = 'event_participants';

    protected $fillable = [
        'event_id',
        'user_id',
        'status',
        'viewed',
    ];

    public $timestamps = true; 
}