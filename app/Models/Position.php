<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Position extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id',
        'driver_id',
        'timestamp',
        'position',
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
