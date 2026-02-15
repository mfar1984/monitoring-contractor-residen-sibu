<?php

if (!function_exists('app_setting')) {
    /**
     * Get application setting value
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    function app_setting($key, $default = null)
    {
        static $settings = null;
        
        if ($settings === null) {
            $settings = \App\Models\IntegrationSetting::getSettings('application');
        }
        
        return $settings[$key] ?? $default;
    }
}

if (!function_exists('items_per_page')) {
    /**
     * Get items per page setting
     *
     * @return int
     */
    function items_per_page()
    {
        return (int) app_setting('items_per_page', 10);
    }
}

if (!function_exists('localization_setting')) {
    /**
     * Get localization setting value
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    function localization_setting($key, $default = null)
    {
        static $settings = null;
        
        if ($settings === null) {
            $settings = \App\Models\IntegrationSetting::getSettings('localization');
        }
        
        return $settings[$key] ?? $default;
    }
}

if (!function_exists('format_date')) {
    /**
     * Format date according to localization settings
     *
     * @param string|null $date
     * @param string|null $format
     * @return string
     */
    function format_date($date = null, $format = null)
    {
        if (!$date) {
            $date = now();
        }
        
        if (!$format) {
            $format = localization_setting('date_format', 'd/m/Y');
        }
        
        return \Carbon\Carbon::parse($date)->format($format);
    }
}

if (!function_exists('format_time')) {
    /**
     * Format time according to localization settings
     *
     * @param string|null $time
     * @param string|null $format
     * @return string
     */
    function format_time($time = null, $format = null)
    {
        if (!$time) {
            $time = now();
        }
        
        if (!$format) {
            $format = localization_setting('time_format', 'H:i:s');
        }
        
        return \Carbon\Carbon::parse($time)->format($format);
    }
}

if (!function_exists('format_datetime')) {
    /**
     * Format datetime according to localization settings
     *
     * @param string|null $datetime
     * @return string
     */
    function format_datetime($datetime = null)
    {
        if (!$datetime) {
            $datetime = now();
        }
        
        $dateFormat = localization_setting('date_format', 'd/m/Y');
        $timeFormat = localization_setting('time_format', 'H:i:s');
        
        return \Carbon\Carbon::parse($datetime)->format($dateFormat . ' ' . $timeFormat);
    }
}

if (!function_exists('format_currency')) {
    /**
     * Format currency according to localization settings
     *
     * @param float $amount
     * @param string|null $currency
     * @return string
     */
    function format_currency($amount, $currency = null)
    {
        if (!$currency) {
            $currency = localization_setting('currency', 'MYR');
        }
        
        $symbols = [
            'MYR' => 'RM',
            'USD' => '$',
            'SGD' => 'S$',
            'EUR' => '€',
        ];
        
        $symbol = $symbols[$currency] ?? $currency;
        
        return $symbol . ' ' . number_format($amount, 2);
    }
}

if (!function_exists('localization_setting')) {
    /**
     * Get localization setting value
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    function localization_setting($key, $default = null)
    {
        static $settings = null;
        
        if ($settings === null) {
            $settings = \App\Models\IntegrationSetting::getSettings('localization');
        }
        
        return $settings[$key] ?? $default;
    }
}

if (!function_exists('format_date')) {
    /**
     * Format date according to localization settings
     *
     * @param string|null $date
     * @return string
     */
    function format_date($date)
    {
        if (!$date) return '';
        
        $format = localization_setting('date_format', 'd/m/Y');
        return date($format, strtotime($date));
    }
}

if (!function_exists('format_time')) {
    /**
     * Format time according to localization settings
     *
     * @param string|null $time
     * @return string
     */
    function format_time($time)
    {
        if (!$time) return '';
        
        $format = localization_setting('time_format', 'H:i:s');
        return date($format, strtotime($time));
    }
}

if (!function_exists('format_datetime')) {
    /**
     * Format datetime according to localization settings
     *
     * @param string|null $datetime
     * @return string
     */
    function format_datetime($datetime)
    {
        if (!$datetime) return '';
        
        $dateFormat = localization_setting('date_format', 'd/m/Y');
        $timeFormat = localization_setting('time_format', 'H:i:s');
        return date($dateFormat . ' ' . $timeFormat, strtotime($datetime));
    }
}

if (!function_exists('format_currency')) {
    /**
     * Format currency according to localization settings
     *
     * @param float $amount
     * @return string
     */
    function format_currency($amount)
    {
        $currency = localization_setting('currency', 'MYR');
        
        $symbols = [
            'MYR' => 'RM',
            'USD' => '$',
            'SGD' => 'S$',
            'EUR' => '€',
        ];
        
        $symbol = $symbols[$currency] ?? $currency;
        return $symbol . ' ' . number_format($amount, 2);
    }
}
