<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Get contractor ID 1 for testing
$contractor = \App\Models\ContractorCategory::find(1);

echo "=== TESTING EXPORT FORMAT ===\n\n";
echo "Company: {$contractor->company_name} ({$contractor->code})\n\n";

// Parse shareholders data
$shareholders = $contractor->getShareholders();
$companyShareholders = [];
$individualShareholders = [];

foreach ($shareholders as $shareholder) {
    if (($shareholder['type'] ?? 'individual') === 'company') {
        // Company shareholder: Name (RegNo, Shares%)
        $companyShareholders[] = sprintf(
            '%s (%s, %s%%)',
            $shareholder['name'] ?? '',
            $shareholder['registration_no'] ?? '',
            $shareholder['shares'] ?? '0'
        );
    } else {
        // Individual shareholder: Name (IC, Shares%)
        $individualShareholders[] = sprintf(
            '%s (%s, %s%%)',
            $shareholder['name'] ?? '',
            $shareholder['ic_number'] ?? '',
            $shareholder['shares'] ?? '0'
        );
    }
}

// Join with pipe separator
$companyShareholdersStr = implode('|', $companyShareholders);
$individualShareholdersStr = implode('|', $individualShareholders);

echo "Company Shareholders (pipe-separated):\n";
echo $companyShareholdersStr . "\n\n";

echo "Individual Shareholders (pipe-separated):\n";
echo $individualShareholdersStr . "\n\n";

echo "=== CSV ROW PREVIEW ===\n";
echo "Code: {$contractor->code}\n";
echo "Company Name: {$contractor->company_name}\n";
echo "Authorized Person: {$contractor->authorized_person_name} ({$contractor->authorized_person_ic})\n";
echo "Company Shareholders: {$companyShareholdersStr}\n";
echo "Individual Shareholders: {$individualShareholdersStr}\n";

echo "\n=== TEST COMPLETE ===\n";
