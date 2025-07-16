<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Position extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass-assignable.
     */
    protected $fillable = [
        'session_id',
        'driver_id',
        'date',
        'position',
    ];

    /**
     * The attribute type casts.
     */
    protected $casts = [
        'date'     => 'datetime',  // will convert your ISO timestamp into a Carbon instance
        'position' => 'integer',
    ];

    /**
     * A Position belongs to a Session.
     */
    public function session()
    {
        return $this->belongsTo(Session::class);
    }

    /**
     * A Position belongs to a Driver.
     */
    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }
}
