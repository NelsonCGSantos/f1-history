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

    /**
     * Retrieve the latest recorded position for each driver in the session.
     *
     * @return \Illuminate\Support\Collection<int, array{position:int, recorded_at:\Illuminate\Support\Carbon}>
     */
    public static function finalClassificationForSession(int $sessionId)
    {
        return static::where('session_id', $sessionId)
            ->orderBy('driver_id')
            ->orderByDesc('date')
            ->get(['driver_id', 'position', 'date'])
            ->unique('driver_id')
            ->mapWithKeys(function ($entry) {
                return [
                    $entry->driver_id => [
                        'position'    => (int) $entry->position,
                        'recorded_at' => $entry->date,
                    ],
                ];
            });
    }
}
