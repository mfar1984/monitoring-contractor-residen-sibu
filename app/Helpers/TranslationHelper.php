<?php

namespace App\Helpers;

use App\Models\IntegrationSetting;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class TranslationHelper
{
    /**
     * Get translation for a given key based on current locale
     * 
     * @param string $key Translation key
     * @param string|null $default Default value if translation not found
     * @return string Translated text or default value
     */
    public static function trans(string $key, ?string $default = null): string
    {
        // Get current locale - prioritize user locale, then app locale, then system locale
        $locale = app()->getLocale();
        
        // If user is authenticated, check their personal locale setting
        if (Auth::check()) {
            $user = Auth::user();
            $userSettings = IntegrationSetting::getSettings('user_' . $user->id);
            if (isset($userSettings['locale']) && $userSettings['locale']) {
                $locale = $userSettings['locale'];
            }
        }
        
        // If no user locale, fall back to system-wide locale
        if (!$locale || $locale === 'en') {
            $locale = IntegrationSetting::getSetting('localization', 'locale') ?? 'en';
        }
        
        // Get translation from database for user's locale
        $translation = IntegrationSetting::getSetting('translation_' . $locale, $key);
        
        // If translation not found in user's locale, try English as fallback
        if (!$translation && $locale !== 'en') {
            $translation = IntegrationSetting::getSetting('translation_en', $key);
            
            // Log missing translation for admin review
            if (!$translation) {
                Log::info("Missing translation for key '{$key}' in locale '{$locale}'");
            }
        }
        
        // Return translation if found, otherwise return default or key
        return $translation ?? $default ?? $key;
    }
    
    /**
     * Get all translations for current locale
     * 
     * @return array All translations
     */
    public static function all(): array
    {
        $locale = app()->getLocale();
        
        // If user is authenticated, check their personal locale setting
        if (Auth::check()) {
            $user = Auth::user();
            $userSettings = IntegrationSetting::getSettings('user_' . $user->id);
            if (isset($userSettings['locale']) && $userSettings['locale']) {
                $locale = $userSettings['locale'];
            }
        }
        
        // If no user locale, fall back to system-wide locale
        if (!$locale || $locale === 'en') {
            $locale = IntegrationSetting::getSetting('localization', 'locale') ?? 'en';
        }
        
        return IntegrationSetting::getSettings('translation_' . $locale);
    }
}
