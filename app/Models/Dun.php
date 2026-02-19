<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Dun extends Model
{
    protected $fillable = [
        'name',
        'code',
        'description',
        'status',
    ];

    /**
     * Get all budget entries for this dun
     */
    public function budgets()
    {
        return $this->hasMany(DunBudget::class);
    }

    /**
     * Get budget amount for a specific year
     * 
     * @param int $year The fiscal year
     * @return float The budget amount for the year, or 0 if not found
     */
    public function getBudgetForYear($year)
    {
        return $this->budgets()
            ->where('year', $year)
            ->first()?->budget ?? 0;
    }

    /**
     * Get all years that have budget entries
     * 
     * @return array Array of years in ascending order
     */
    public function getAllYears()
    {
        return $this->budgets()
            ->orderBy('year')
            ->pluck('year')
            ->toArray();
    }
}
