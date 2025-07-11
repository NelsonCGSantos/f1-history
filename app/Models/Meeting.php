<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Meeting extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'season_year',
        'location',
        'country',
        'start_date',
        'end_date',
    ];

    public function sessions()
    {
        return $this->hasMany(Session::class);
    }
}
