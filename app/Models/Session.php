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

    protected $casts = [
        'start_time' => 'datetime',
        'end_time'   => 'datetime',
    ];

    public function meeting()
    {
        return $this->belongsTo(Meeting::class);
    }

    public function laps()
    {
        return $this->hasMany(Lap::class);
    }

    public function positions()
    {
        return $this->hasMany(Position::class);
    }

    public function stints()
    {
        return $this->hasMany(Stint::class);
    }
}
