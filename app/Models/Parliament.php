<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Parliament extends Model
{
    protected $fillable = [
        'name',
        'budget',
        'code',
        'description',
        'status',
    ];
}
