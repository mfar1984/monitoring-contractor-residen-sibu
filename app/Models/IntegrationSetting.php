<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IntegrationSetting extends Model
{
    protected $fillable = [
        'type',
        'key',
        'value',
    ];

    public static function getSetting($type, $key, $default = null)
    {
        $setting = self::where('type', $type)->where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }

    public static function setSetting($type, $key, $value)
    {
        // Encrypt sensitive fields
        $sensitiveFields = ['password', 'api_key', 'secret', 'smtp_password', 'api_secret', 'webhook_secret'];
        
        $shouldEncrypt = false;
        foreach ($sensitiveFields as $field) {
            if (stripos($key, $field) !== false) {
                $shouldEncrypt = true;
                break;
            }
        }
        
        if ($shouldEncrypt && $value) {
            $value = encrypt($value);
        }
        
        return self::updateOrCreate(
            ['type' => $type, 'key' => $key],
            ['value' => $value]
        );
    }

    public static function getSettings($type)
    {
        $settings = self::where('type', $type)->get();
        $result = [];
        
        $sensitiveFields = ['password', 'api_key', 'secret', 'smtp_password', 'api_secret', 'webhook_secret'];
        
        foreach ($settings as $setting) {
            $shouldDecrypt = false;
            foreach ($sensitiveFields as $field) {
                if (stripos($setting->key, $field) !== false) {
                    $shouldDecrypt = true;
                    break;
                }
            }
            
            try {
                $result[$setting->key] = $shouldDecrypt && $setting->value ? decrypt($setting->value) : $setting->value;
            } catch (\Exception $e) {
                $result[$setting->key] = $setting->value;
            }
        }
        
        return $result;
    }
}
