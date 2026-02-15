<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\IntegrationSetting;

class EncryptIntegrationSettings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'integrations:encrypt';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Encrypt all sensitive integration settings that are currently stored in plain text';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting encryption of integration settings...');
        
        $sensitiveFields = ['password', 'api_key', 'secret', 'smtp_password', 'api_secret', 'webhook_secret'];
        $settings = IntegrationSetting::all();
        $encrypted = 0;
        $skipped = 0;
        
        foreach ($settings as $setting) {
            $shouldEncrypt = false;
            foreach ($sensitiveFields as $field) {
                if (stripos($setting->key, $field) !== false) {
                    $shouldEncrypt = true;
                    break;
                }
            }
            
            if ($shouldEncrypt && $setting->value) {
                // Check if already encrypted
                try {
                    decrypt($setting->value);
                    $this->line("Skipping {$setting->type}.{$setting->key} - already encrypted");
                    $skipped++;
                } catch (\Exception $e) {
                    // Not encrypted, encrypt it now
                    $setting->value = encrypt($setting->value);
                    $setting->save();
                    $this->info("Encrypted {$setting->type}.{$setting->key}");
                    $encrypted++;
                }
            }
        }
        
        $this->info("\nEncryption complete!");
        $this->info("Encrypted: {$encrypted} settings");
        $this->info("Skipped: {$skipped} settings (already encrypted)");
        
        return 0;
    }
}
