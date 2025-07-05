<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Constructor extends Model
{
    protected $fillable = ['constructor_id', 'name', 'nationality'];

    public function results()
    {
        return $this->hasMany(Result::class, 'constructor_id', 'constructor_id');
    }
}
