<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Division extends Model
{
    protected $fillable = [
        'name',
        'code',
        'description',
        'status',
    ];

    public function districts()
    {
        return $this->hasMany(District::class);
    }
}
