<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CarData extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id',
        'driver_id',
        'timestamp',
        'speed',
        'throttle',
        'brake',
        'drs',
        'gear',
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
