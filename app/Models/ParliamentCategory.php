<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ParliamentCategory extends Model
{
    protected $fillable = [
        'name',
        'budget',
        'code',
        'type',
        'description',
        'status',
    ];
}
