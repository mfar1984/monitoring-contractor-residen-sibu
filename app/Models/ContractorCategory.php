<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContractorCategory extends Model
{
    protected $fillable = [
        'company_name',
        'code',
        'registration_number',
        'description',
        'status',
    ];
}
