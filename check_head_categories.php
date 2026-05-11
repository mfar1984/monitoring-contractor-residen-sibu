<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\UpkjClassification;

echo "=== Checking Categories for Each Head ===\n\n";

$heads = ['I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII'];

foreach ($heads as $head) {
    $sample = UpkjClassification::where('head_code', $head)->first();
    
    if ($sample) {
        echo "Head $head:\n";
        echo "  Category: {$sample->category}\n";
        echo "  Head Name: {$sample->head_name}\n\n";
    } else {
        echo "Head $head: No data found\n\n";
    }
}
