<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Driver extends Model
{
    use HasFactory;

    protected $fillable = [
        'driver_number',
        'name',
        'team_name',
        'nationality',
        'abbreviation',
    ];

    // Relationships
    public function laps()
    {
        return $this->hasMany(Lap::class);
    }

    public function positions()
    {
        return $this->hasMany(Position::class);
    }

    public function stints()
    {
        return $this->hasMany(Stint::class);
    }

    public function carData()
    {
        return $this->hasMany(CarData::class);
    }

    public function intervals()
    {
        return $this->hasMany(Interval::class);
    }
}
