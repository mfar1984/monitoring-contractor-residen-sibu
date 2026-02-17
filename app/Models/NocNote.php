<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NocNote extends Model
{
    protected $fillable = [
        'name',
        'code',
        'description',
        'status',
    ];
}
