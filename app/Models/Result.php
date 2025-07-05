<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Result extends Model
{
    protected $fillable = [
        'race_id', 'driver_id', 'constructor_id',
        'grid', 'position', 'laps', 'status', 'time'
    ];

    public function race()
    {
        return $this->belongsTo(Race::class);
    }

    public function driver()
    {
        return $this->belongsTo(Driver::class, 'driver_id', 'driver_id');
    }

    public function constructor()
    {
        return $this->belongsTo(Constructor::class, 'constructor_id', 'constructor_id');
    }
}
