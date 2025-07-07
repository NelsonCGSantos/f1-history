<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Session extends Model
{
    use HasFactory;

    protected $fillable = [
        'meeting_id',
        'type',
        'session_key',
        'start_time',
        'end_time',
    ];

    public function meeting()
    {
        return $this->belongsTo(Meeting::class);
    }
}
