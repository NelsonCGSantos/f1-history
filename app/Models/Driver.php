<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Driver extends Model
{
    protected $fillable = [
        'driver_id', 'given_name', 'family_name',
        'date_of_birth', 'nationality',
    ];

    public function results()
    {
        return $this->hasMany(Result::class, 'driver_id', 'driver_id');
    }

    public function championships()
    {
        return $this->hasMany(Season::class, 'champion_driver_id', 'driver_id');
    }
}
