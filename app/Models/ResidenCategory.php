<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ResidenCategory extends Model
{
    protected $fillable = [
        'name',
        'code',
        'description',
        'status',
    ];
}
