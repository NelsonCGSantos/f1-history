<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RaceControl extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id',
        'timestamp',
        'event_type',
        'message',
    ];

    protected $casts = [
        'timestamp' => 'integer',
    ];

    public function session()
    {
        return $this->belongsTo(Session::class);
    }
}
