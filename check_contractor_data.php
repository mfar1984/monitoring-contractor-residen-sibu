<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Check contractor ID 1 data structure
$contractor = DB::table('contractor_categories')->where('id', 1)->first();

echo "=== CONTRACTOR ID 1 DATA STRUCTURE ===\n\n";
echo "Company Name: " . $contractor->company_name . "\n";
echo "Code: " . $contractor->code . "\n\n";

echo "Authorized Person Name: " . ($contractor->authorized_person_name ?? 'NULL') . "\n";
echo "Authorized Person IC: " . ($contractor->authorized_person_ic ?? 'NULL') . "\n\n";

echo "Shareholders Data (JSON):\n";
if (!empty($contractor->shareholders_data)) {
    $shareholders = json_decode($contractor->shareholders_data, true);
    echo json_encode($shareholders, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    echo "\n\n";
    
    // Separate by type
    $companies = [];
    $individuals = [];
    
    foreach ($shareholders as $shareholder) {
        if (($shareholder['type'] ?? 'individual') === 'company') {
            $companies[] = $shareholder;
        } else {
            $individuals[] = $shareholder;
        }
    }
    
    echo "\n=== COMPANY AS SHAREHOLDER (type=company) ===\n";
    echo "Count: " . count($companies) . "\n";
    if (!empty($companies)) {
        echo json_encode($companies, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
    
    echo "\n\n=== SHAREHOLDERS/DIRECTORS (type=individual or no type) ===\n";
    echo "Count: " . count($individuals) . "\n";
    if (!empty($individuals)) {
        echo json_encode($individuals, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
} else {
    echo "NULL or EMPTY\n";
}

echo "\n\n=== VERIFICATION COMPLETE ===\n";
