<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Stint extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id',
        'driver_id',
        'start_lap',
        'end_lap',
        'tire_compound',
        'stint_number',
        'tyre_age_at_start',
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
