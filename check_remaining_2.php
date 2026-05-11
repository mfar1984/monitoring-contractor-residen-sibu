<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\ContractorUpkjRecord;

echo "=== Checking Remaining 2 Incomplete Records ===\n\n";

$records = ContractorUpkjRecord::all();

$incompleteRecords = [];

foreach ($records as $record) {
    $classifications = $record->classifications;
    
    if (!is_array($classifications)) {
        continue;
    }
    
    $hasIncomplete = false;
    $incompleteClassifications = [];
    
    foreach ($classifications as $c) {
        if (empty($c['head_name']) || empty($c['description'])) {
            $hasIncomplete = true;
            $incompleteClassifications[] = $c;
        }
    }
    
    if ($hasIncomplete) {
        $incompleteRecords[] = [
            'record' => $record,
            'incomplete' => $incompleteClassifications
        ];
    }
}

echo "Found " . count($incompleteRecords) . " records with incomplete classifications:\n\n";

foreach ($incompleteRecords as $item) {
    $record = $item['record'];
    $incomplete = $item['incomplete'];
    
    echo "Record ID: {$record->id}\n";
    echo "Contractor: {$record->contractor->name}\n";
    echo "Category: {$record->category}\n";
    echo "Incomplete classifications:\n";
    
    foreach ($incomplete as $c) {
        $code = $c['class'] . '-' . $c['head_code'] . '-' . $c['subhead_code'];
        if (!empty($c['subhead_roman'])) {
            $code .= $c['subhead_roman'];
        }
        if (!empty($c['subhead_letter'])) {
            $code .= $c['subhead_letter'];
        }
        
        echo "  - $code\n";
        echo "    Class: {$c['class']}\n";
        echo "    Head Code: {$c['head_code']}\n";
        echo "    Subhead Code: {$c['subhead_code']}\n";
        echo "    Subhead Roman: " . ($c['subhead_roman'] ?: 'NULL') . "\n";
        echo "    Subhead Letter: " . ($c['subhead_letter'] ?: 'NULL') . "\n";
    }
    
    echo "\n";
}
