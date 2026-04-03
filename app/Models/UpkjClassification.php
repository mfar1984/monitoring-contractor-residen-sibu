<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UpkjClassification extends Model
{
    protected $fillable = [
        'category',
        'class',
        'class_description',
        'head_code',
        'head_name',
        'subhead_code',
        'subhead_letter',
        'subhead_roman',
        'description',
        'status',
    ];

    /**
     * Get unique categories
     */
    public static function getCategories()
    {
        return self::select('category')
            ->distinct()
            ->orderBy('category')
            ->pluck('category');
    }

    /**
     * Get unique classes
     */
    public static function getClasses()
    {
        return self::select('class', 'class_description')
            ->distinct()
            ->orderBy('class')
            ->get();
    }

    /**
     * Get unique head codes
     */
    public static function getHeadCodes()
    {
        return self::select('head_code', 'head_name')
            ->distinct()
            ->orderBy('head_code')
            ->get();
    }

    /**
     * Get unique subhead codes
     */
    public static function getSubheadCodes()
    {
        return self::select('subhead_code')
            ->distinct()
            ->whereNotNull('subhead_code')
            ->orderBy('subhead_code')
            ->pluck('subhead_code');
    }

    /**
     * Get unique subhead letters
     */
    public static function getSubheadLetters()
    {
        return self::select('subhead_letter')
            ->distinct()
            ->whereNotNull('subhead_letter')
            ->where('subhead_letter', '!=', '')
            ->orderBy('subhead_letter')
            ->pluck('subhead_letter');
    }

    /**
     * Get unique subhead romans
     */
    public static function getSubheadRomans()
    {
        return self::select('subhead_roman')
            ->distinct()
            ->whereNotNull('subhead_roman')
            ->where('subhead_roman', '!=', '')
            ->orderBy('subhead_roman')
            ->pluck('subhead_roman');
    }
}
