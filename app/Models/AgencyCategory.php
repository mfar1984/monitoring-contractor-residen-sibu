<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AgencyCategory extends Model
{
    protected $fillable = [
        'name',
        'code',
        'description',
        'status',
    ];
}
