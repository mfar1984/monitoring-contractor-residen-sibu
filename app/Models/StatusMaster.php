<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StatusMaster extends Model
{
    protected $table = 'status_master';
    
    protected $fillable = [
        'name',
        'code',
        'color',
        'description',
        'status',
    ];
}
