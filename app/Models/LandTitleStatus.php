<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LandTitleStatus extends Model
{
    protected $fillable = [
        'name',
        'code',
        'description',
        'status',
    ];
}
