<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Standing extends Model
{
    protected $fillable = [
        'season_id', 'driver_id', 'constructor_id',
        'position', 'points'
    ];

    public function season()
    {
        return $this->belongsTo(Season::class);
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
