<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Language extends Model
{
    protected $fillable = [
        'code',
        'name',
        'is_default',
        'status'
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    /**
     * Get all active languages
     */
    public static function getActiveLanguages()
    {
        return self::where('status', 'Active')->orderBy('is_default', 'desc')->orderBy('name')->get();
    }

    /**
     * Get default languages (en, ms, zh)
     */
    public static function getDefaultLanguages()
    {
        return self::where('is_default', true)->get();
    }

    /**
     * Check if language code exists
     */
    public static function codeExists($code)
    {
        return self::where('code', $code)->exists();
    }
}
