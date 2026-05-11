<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== TESTING IMPORT PARSE LOGIC ===\n\n";

// Test data
$testData = [
    'Company Shareholders' => 'XYZ Holdings Sdn Bhd (ROC987654, 60%)|ABC Ventures (ROC456789, 40%)',
    'Individual Shareholders' => 'Ahmad bin Ali (800101-13-5678, 50%)|Siti binti Hassan (850202-13-1234, 50%)',
];

$shareholdersData = [];

// Parse Company Shareholders
if (!empty($testData['Company Shareholders'])) {
    $companyShareholdersList = explode('|', $testData['Company Shareholders']);
    foreach ($companyShareholdersList as $shareholderStr) {
        $shareholderStr = trim($shareholderStr);
        if (empty($shareholderStr)) continue;
        
        // Parse format: Name (RegNo, Shares%)
        if (preg_match('/^(.+?)\s*\(([^,]+),\s*(\d+(?:\.\d+)?)%\)$/', $shareholderStr, $matches)) {
            $shareholdersData[] = [
                'type' => 'company',
                'name' => trim($matches[1]),
                'registration_no' => trim($matches[2]),
                'shares' => trim($matches[3]),
            ];
        }
    }
}

// Parse Individual Shareholders
if (!empty($testData['Individual Shareholders'])) {
    $individualShareholdersList = explode('|', $testData['Individual Shareholders']);
    foreach ($individualShareholdersList as $shareholderStr) {
        $shareholderStr = trim($shareholderStr);
        if (empty($shareholderStr)) continue;
        
        // Parse format: Name (IC, Shares%)
        if (preg_match('/^(.+?)\s*\(([^,]+),\s*(\d+(?:\.\d+)?)%\)$/', $shareholderStr, $matches)) {
            $shareholdersData[] = [
                'type' => 'individual',
                'name' => trim($matches[1]),
                'ic_number' => trim($matches[2]),
                'shares' => trim($matches[3]),
            ];
        }
    }
}

echo "Parsed Shareholders Data:\n";
echo json_encode($shareholdersData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

echo "\n\n=== TEST COMPLETE ===\n";
