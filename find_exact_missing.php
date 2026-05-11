<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\UpkjClassification;

echo "=== Finding EXACT missing codes ===\n\n";

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
    
    // Parse subhead: 2iia => code=2, roman=ii, letter=a
    if (preg_match('/^(\d+)(i+)([a-z]*)$/', $subhead, $matches)) {
        $subhead_code = $matches[1];
        $subhead_roman = $matches[2];
        $subhead_letter = $matches[3] ?: null;
        
        echo "Code: $code\n";
        echo "  Parsed: Class=$class, Head=$head, Subhead=$subhead_code, Roman=$subhead_roman, Letter=$subhead_letter\n";
        
        // Search with roman numeral
        $found = UpkjClassification::where('class', $class)
            ->where('head_code', $head)
            ->where('subhead_code', $subhead_code)
            ->where('subhead_roman', $subhead_roman)
            ->where(function($q) use ($subhead_letter) {
                if ($subhead_letter) {
                    $q->where('subhead_letter', $subhead_letter);
                } else {
                    $q->whereNull('subhead_letter');
                }
            })
            ->first();
        
        if ($found) {
            echo "  ✅ FOUND: {$found->description}\n";
        } else {
            echo "  ❌ NOT FOUND\n";
            
            // Check what exists for this class-head-subhead
            $existing = UpkjClassification::where('class', $class)
                ->where('head_code', $head)
                ->where('subhead_code', $subhead_code)
                ->get();
            
            if ($existing->count() > 0) {
                echo "  Existing codes for $class-$head-$subhead_code:\n";
                foreach ($existing as $ex) {
                    $romanPart = $ex->subhead_roman ?: '';
                    $letterPart = $ex->subhead_letter ?: '';
                    echo "    - $class-$head-$subhead_code$romanPart$letterPart: {$ex->description}\n";
                }
            } else {
                echo "  No codes exist for $class-$head-$subhead_code\n";
            }
        }
        echo "\n";
    }
}
