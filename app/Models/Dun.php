<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Dun extends Model
{
    protected $fillable = [
        'name',
        'budget',
        'code',
        'description',
        'status',
    ];
}
