<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Interval extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id',
        'driver_id',
        'timestamp',
        'gap_to_leader',
    ];

    protected $casts = [
        'timestamp' => 'integer',
        'gap_to_leader' => 'float',
    ];

    public function session()
    {
        return $this->belongsTo(Session::class);
    }

    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }
}
