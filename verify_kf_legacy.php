<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\ContractorCategory;
use App\Models\ContractorUpkjRecord;

echo "=== Verifying KF Legacy Resources ===\n\n";

$contractor = ContractorCategory::where('company_name', 'LIKE', '%KF Legacy%')->first();

if (!$contractor) {
    echo "KF Legacy Resources not found!\n";
    exit;
}

echo "Contractor: {$contractor->company_name}\n";
echo "Code: {$contractor->code}\n\n";

$records = ContractorUpkjRecord::where('contractor_category_id', $contractor->id)->get();

echo "Total UPKJ records: {$records->count()}\n\n";

foreach ($records as $record) {
    echo "Category: {$record->category}\n";
    echo "Total classifications: " . count($record->classifications) . "\n";
    
    $incomplete = 0;
    $complete = 0;
    
    foreach ($record->classifications as $c) {
        if (empty($c['head_name']) || empty($c['description'])) {
            $incomplete++;
        } else {
            $complete++;
        }
    }
    
    echo "Complete: $complete\n";
    echo "Incomplete: $incomplete\n";
    
    if ($incomplete > 0) {
        echo "❌ Still has incomplete classifications!\n";
        echo "Incomplete codes:\n";
        foreach ($record->classifications as $c) {
            if (empty($c['head_name']) || empty($c['description'])) {
                $code = $c['class'] . '-' . $c['head_code'] . '-' . $c['subhead_code'];
                if (!empty($c['subhead_roman'])) {
                    $code .= $c['subhead_roman'];
                }
                if (!empty($c['subhead_letter'])) {
                    $code .= $c['subhead_letter'];
                }
                echo "  - $code\n";
            }
        }
    } else {
        echo "✅ All classifications complete!\n";
    }
    
    echo "\n";
}
