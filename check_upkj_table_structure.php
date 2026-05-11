<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\UpkjClassification;

echo "=== Checking UPKJ Classifications Table Structure ===\n\n";

$sample = UpkjClassification::first();

if ($sample) {
    echo "Columns in upkj_classifications table:\n\n";
    
    $attributes = $sample->getAttributes();
    
    foreach ($attributes as $key => $value) {
        $displayValue = is_null($value) ? 'NULL' : (strlen($value) > 50 ? substr($value, 0, 50) . '...' : $value);
        echo "  - $key: $displayValue\n";
    }
} else {
    echo "No data found in upkj_classifications table!\n";
}
