<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'username',
        'full_name',
        'residen_category_id',
        'agency_category_id',
        'parliament_category_id',
        'parliament_id',
        'dun_id',
        'contractor_category_id',
        'department',
        'contact_number',
        'email',
        'password',
        'role',
        'status',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    /**
     * Get the residen category that owns the user.
     */
    public function residenCategory()
    {
        return $this->belongsTo(\App\Models\ResidenCategory::class);
    }

    /**
     * Get the agency category that owns the user.
     */
    public function agencyCategory()
    {
        return $this->belongsTo(\App\Models\AgencyCategory::class);
    }

    /**
     * Get the parliament category that owns the user.
     */
    public function parliamentCategory()
    {
        return $this->belongsTo(\App\Models\ParliamentCategory::class);
    }

    /**
     * Get the contractor category that owns the user.
     */
    public function contractorCategory()
    {
        return $this->belongsTo(\App\Models\ContractorCategory::class);
    }

    /**
     * Get the parliament that owns the user.
     */
    public function parliament()
    {
        return $this->belongsTo(\App\Models\Parliament::class);
    }

    /**
     * Get the dun that owns the user.
     */
    public function dun()
    {
        return $this->belongsTo(\App\Models\Dun::class);
    }
}
