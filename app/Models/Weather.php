<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Weather extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id',
        'timestamp',
        'air_temp',
        'track_temp',
        'humidity',
        'wind_speed',
        'precipitation',
    ];

    protected $casts = [
        'timestamp' => 'integer',
        'air_temp' => 'float',
        'track_temp' => 'float',
        'humidity' => 'float',
        'wind_speed' => 'float',
        'precipitation' => 'float',
    ];

    public function session()
    {
        return $this->belongsTo(Session::class);
    }
}
