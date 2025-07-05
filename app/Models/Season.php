<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Season extends Model
{
    protected $fillable = ['year', 'champion_driver_id'];

    public function races()
    {
        return $this->hasMany(Race::class);
    }

    public function standings()
    {
        return $this->hasMany(Standing::class);
    }

    public function champion()
    {
        return $this->belongsTo(Driver::class, 'champion_driver_id', 'driver_id');
    }
}
