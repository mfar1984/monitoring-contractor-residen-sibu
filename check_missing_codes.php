<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\UpkjClassification;

echo "Checking specific missing classification codes:\n\n";

$missingCodes = [
    'E-I-2iia', 'E-I-2iib', 'E-I-2iic', 'E-I-3iia',
    'F-I-2ia', 'F-I-2iia', 'F-I-2ib', 'F-I-2iib', 'F-I-2iic', 'F-I-3ia', 'F-I-3iia',
    'E-VI-5ia', 'E-VI-5iia',
    'D-VI-5ia', 'D-VI-5iia'
];

foreach ($missingCodes as $code) {
    $parts = explode('-', $code);
    $class = $parts[0];
    $head = $parts[1];
    $subhead = $parts[2] ?? null;
    
    // Parse subhead into code and letter
    // Examples: 2iia => code=2, letter=iia
    preg_match('/^(\d+)([a-z]*)$/', $subhead, $matches);
    $subhead_code = $matches[1] ?? null;
    $subhead_letter = $matches[2] ?? null;
    
    echo "Code: $code\n";
    echo "  Parsed: Class=$class, Head=$head, Subhead Code=$subhead_code, Letter=$subhead_letter\n";
    
    // Try to find in master data
    $found = UpkjClassification::where('class', $class)
        ->where('head_code', $head)
        ->where('subhead_code', $subhead_code)
        ->where('subhead_letter', $subhead_letter)
        ->first();
    
    if ($found) {
        echo "  ✅ FOUND: {$found->description}\n";
    } else {
        echo "  ❌ NOT FOUND in master data\n";
        
        // Try to find similar codes
        $similar = UpkjClassification::where('class', $class)
            ->where('head_code', $head)
            ->where('subhead_code', $subhead_code)
            ->get();
        
        if ($similar->count() > 0) {
            echo "  Similar codes found:\n";
            foreach ($similar as $s) {
                echo "    - {$s->class}-{$s->head_code}-{$s->subhead_code}{$s->subhead_letter}: {$s->description}\n";
            }
        }
    }
    echo "\n";
}

// Check what roman numeral codes exist
echo "\n=== Checking for roman numeral patterns ===\n\n";

$romanPatterns = UpkjClassification::whereNotNull('subhead_roman')
    ->where('subhead_roman', '!=', '')
    ->get();

echo "Total classifications with roman numerals: " . $romanPatterns->count() . "\n\n";

if ($romanPatterns->count() > 0) {
    echo "Examples:\n";
    foreach ($romanPatterns->take(10) as $pattern) {
        echo "  {$pattern->class}-{$pattern->head_code}-{$pattern->subhead_code}{$pattern->subhead_roman}{$pattern->subhead_letter}: {$pattern->description}\n";
    }
}
