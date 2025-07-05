<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Race extends Model
{
    protected $fillable = ['season_id', 'circuit_id', 'name', 'date'];

    public function season()
    {
        return $this->belongsTo(Season::class);
    }

    public function circuit()
    {
        return $this->belongsTo(Circuit::class);
    }

    public function results()
    {
        return $this->hasMany(Result::class);
    }
}
