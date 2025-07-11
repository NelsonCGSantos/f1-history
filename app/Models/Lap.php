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
        'i1_speed',
        'i2_speed',
        'speed_trap',
        'is_pit_out',
        'segments_sector_1',
        'segments_sector_2',
        'segments_sector_3',
    ];

    protected $casts = [
        'lap_time'        => 'float',
        'sector_1_time'   => 'float',
        'sector_2_time'   => 'float',
        'sector_3_time'   => 'float',
        'i1_speed'        => 'integer',
        'i2_speed'        => 'integer',
        'speed_trap'      => 'integer',
        'is_pit_out'      => 'boolean',
        'segments_sector_1' => 'array',
        'segments_sector_2' => 'array',
        'segments_sector_3' => 'array',
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
