<?php

namespace App\Helpers;

use App\Models\IntegrationSetting;

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
        // Get current locale from settings
        $locale = IntegrationSetting::getSetting('localization', 'locale') ?? 'en';
        
        // Get translation from database
        $translation = IntegrationSetting::getSetting('translation_' . $locale, $key);
        
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
        $locale = IntegrationSetting::getSetting('localization', 'locale') ?? 'en';
        return IntegrationSetting::getSettings('translation_' . $locale);
    }
}
