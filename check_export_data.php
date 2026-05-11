<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Check a few contractors to see their data
$contractors = \App\Models\ContractorCategory::limit(5)->get();

echo "=== CHECKING CONTRACTOR EXPORT DATA ===\n\n";

foreach ($contractors as $contractor) {
    echo "Company: {$contractor->company_name}\n";
    echo "Code: {$contractor->code}\n";
    echo "Authorized Person: {$contractor->authorized_person_name} ({$contractor->authorized_person_ic})\n";
    
    // Check shareholders data
    $shareholders = $contractor->getShareholders();
    echo "Shareholders Data: " . (empty($shareholders) ? 'EMPTY' : count($shareholders) . ' entries') . "\n";
    
    if (!empty($shareholders)) {
        echo "Raw JSON: " . json_encode($shareholders) . "\n";
        
        // Test export format
        $companyShareholders = [];
        $individualShareholders = [];
        
        foreach ($shareholders as $shareholder) {
            if (($shareholder['type'] ?? 'individual') === 'company') {
                $companyShareholders[] = sprintf(
                    '%s (%s, %s%%)',
                    $shareholder['name'] ?? '',
                    $shareholder['registration_no'] ?? '',
                    $shareholder['shares'] ?? '0'
                );
            } else {
                $individualShareholders[] = sprintf(
                    '%s (%s, %s%%)',
                    $shareholder['name'] ?? '',
                    $shareholder['ic_number'] ?? '',
                    $shareholder['shares'] ?? '0'
                );
            }
        }
        
        echo "Company Shareholders Export: " . implode('|', $companyShareholders) . "\n";
        echo "Individual Shareholders Export: " . implode('|', $individualShareholders) . "\n";
    }
    
    echo "\n---\n\n";
}

echo "=== CHECK COMPLETE ===\n";
