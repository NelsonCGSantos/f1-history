<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Lap extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id',
        'driver_id',
        'lap_number',
        'lap_time',
        'sector_1_time',
        'sector_2_time',
        'sector_3_time',
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
