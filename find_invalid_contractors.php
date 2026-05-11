<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\ContractorUpkjRecord;

echo "=== Finding Contractors with Invalid Codes ===\n\n";

// Find records with F-IV-3
echo "1. Searching for F-IV-3:\n";
$records = ContractorUpkjRecord::all();

$foundFIV3 = false;
$foundEXI1 = false;

foreach ($records as $record) {
    $classifications = $record->classifications;
    
    if (!is_array($classifications)) {
        continue;
    }
    
    foreach ($classifications as $c) {
        // Check for F-IV-3
        if ($c['class'] == 'F' && 
            $c['head_code'] == 'IV' && 
            $c['subhead_code'] == '3' &&
            empty($c['subhead_roman']) &&
            empty($c['subhead_letter'])) {
            
            if (!$foundFIV3) {
                echo "   Found in Record ID: {$record->id}\n";
                echo "   Contractor: {$record->contractor->company_name}\n";
                echo "   Code: {$record->contractor->code}\n";
                echo "   Category: {$record->category}\n";
                echo "   Classification: F-IV-3\n\n";
                $foundFIV3 = true;
            }
        }
        
        // Check for EX-I-1
        if ($c['class'] == 'EX' && 
            $c['head_code'] == 'I' && 
            $c['subhead_code'] == '1') {
            
            if (!$foundEXI1) {
                echo "2. Searching for EX-I-1:\n";
                echo "   Found in Record ID: {$record->id}\n";
                echo "   Contractor: {$record->contractor->company_name}\n";
                echo "   Code: {$record->contractor->code}\n";
                echo "   Category: {$record->category}\n";
                echo "   Classification: EX-I-1\n\n";
                $foundEXI1 = true;
            }
        }
    }
}

if (!$foundFIV3) {
    echo "   ❌ F-IV-3 NOT FOUND in any contractor records\n\n";
}

if (!$foundEXI1) {
    echo "2. Searching for EX-I-1:\n";
    echo "   ❌ EX-I-1 NOT FOUND in any contractor records\n\n";
}

echo "=== Explanation ===\n\n";
echo "F-IV-3:\n";
echo "  - Class F = Works - 200,000 And Below\n";
echo "  - Head IV = Works - Irrigation And Drainage Works\n";
echo "  - Subhead 3 = Does NOT exist in UPKJ master data\n\n";

echo "EX-I-1:\n";
echo "  - Class EX = INVALID CLASS (not in UPKJ system)\n";
echo "  - Valid classes: A, B, C, D, E, F, I, II, III, IV\n\n";

echo "These codes were likely entered incorrectly during data import.\n";
