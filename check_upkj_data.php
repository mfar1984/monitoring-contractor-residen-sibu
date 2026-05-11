<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\ContractorUpkjRecord;

echo "=== UPKJ Data Check ===\n\n";

// Get first 5 records
$records = ContractorUpkjRecord::with('contractor')->take(5)->get();

foreach ($records as $record) {
    echo "Company: " . ($record->contractor->company_name ?? 'N/A') . "\n";
    echo "Code: " . ($record->contractor->code ?? 'N/A') . "\n";
    echo "Category: " . $record->category . "\n";
    echo "Classifications: " . json_encode($record->classifications) . "\n";
    echo "---\n";
}

// Count records with empty classifications
$emptyCount = ContractorUpkjRecord::where(function($q) {
    $q->whereNull('classifications')
      ->orWhere('classifications', '[]')
      ->orWhere('classifications', '');
})->count();

echo "\nRecords with empty classifications: $emptyCount\n";

// Count total records
$total = ContractorUpkjRecord::count();
echo "Total records: $total\n";
