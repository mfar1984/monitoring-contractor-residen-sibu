<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Apply application settings
        $this->applyApplicationSettings();
        
        // Apply localization settings
        $this->applyLocalizationSettings();
    }
    
    /**
     * Apply application settings from database
     */
    protected function applyApplicationSettings(): void
    {
        try {
            // Get application settings
            $settings = \App\Models\IntegrationSetting::getSettings('application');
            
            // Apply session lifetime if set
            if (isset($settings['session_lifetime']) && $settings['session_lifetime']) {
                config(['session.lifetime' => (int) $settings['session_lifetime']]);
            }
            
            // Apply app name if set
            if (isset($settings['app_name']) && $settings['app_name']) {
                config(['app.name' => $settings['app_name']]);
            }
            
            // Apply app url if set
            if (isset($settings['app_url']) && $settings['app_url']) {
                config(['app.url' => $settings['app_url']]);
            }
        } catch (\Exception $e) {
            // Silently fail if database is not ready (e.g., during migration)
            \Log::debug('Could not load application settings: ' . $e->getMessage());
        }
    }
    
    /**
     * Apply localization settings from database
     */
    protected function applyLocalizationSettings(): void
    {
        try {
            // Get localization settings
            $settings = \App\Models\IntegrationSetting::getSettings('localization');
            
            // Apply locale if set
            if (isset($settings['locale']) && $settings['locale']) {
                config(['app.locale' => $settings['locale']]);
                app()->setLocale($settings['locale']);
            }
            
            // Apply timezone if set
            if (isset($settings['timezone']) && $settings['timezone']) {
                config(['app.timezone' => $settings['timezone']]);
                date_default_timezone_set($settings['timezone']);
            }
        } catch (\Exception $e) {
            // Silently fail if database is not ready
            \Log::debug('Could not load localization settings: ' . $e->getMessage());
        }
    }
}
