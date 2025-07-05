<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Circuit extends Model
{
    protected $fillable = ['circuit_id', 'name', 'location', 'lat', 'lng'];

    public function races()
    {
        return $this->hasMany(Race::class);
    }
}
