<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\UpkjClassification;

echo "=== Analyzing Roman Numeral Classifications by Class ===\n\n";

$classes = ['A', 'B', 'C', 'D', 'E', 'F'];

foreach ($classes as $class) {
    $withRoman = UpkjClassification::where('class', $class)
        ->whereNotNull('subhead_roman')
        ->where('subhead_roman', '!=', '')
        ->count();
    
    $total = UpkjClassification::where('class', $class)->count();
    
    echo "Class $class: $withRoman roman numeral codes out of $total total\n";
    
    if ($withRoman > 0) {
        // Show examples
        $examples = UpkjClassification::where('class', $class)
            ->whereNotNull('subhead_roman')
            ->where('subhead_roman', '!=', '')
            ->take(5)
            ->get();
        
        foreach ($examples as $ex) {
            echo "  - {$ex->class}-{$ex->head_code}-{$ex->subhead_code}{$ex->subhead_roman}{$ex->subhead_letter}: {$ex->description}\n";
        }
    }
    echo "\n";
}

echo "\n=== Checking specific patterns ===\n\n";

// Check if E-I-2iia pattern exists in Class A
$classA = UpkjClassification::where('class', 'A')
    ->where('head_code', 'I')
    ->where('subhead_code', '2')
    ->where('subhead_roman', 'ii')
    ->where('subhead_letter', 'a')
    ->first();

if ($classA) {
    echo "✅ Class A has I-2iia: {$classA->description}\n";
} else {
    echo "❌ Class A does NOT have I-2iia\n";
}

// Check if E-I-2iia exists in Class E
$classE = UpkjClassification::where('class', 'E')
    ->where('head_code', 'I')
    ->where('subhead_code', '2')
    ->where('subhead_roman', 'ii')
    ->where('subhead_letter', 'a')
    ->first();

if ($classE) {
    echo "✅ Class E has I-2iia: {$classE->description}\n";
} else {
    echo "❌ Class E does NOT have I-2iia\n";
}

echo "\n=== Conclusion ===\n\n";
echo "The missing codes like E-I-2iia, F-I-2iia, D-VI-5ia are using roman numeral patterns\n";
echo "that exist in Class A/B/C but NOT in Class D/E/F.\n\n";
echo "This suggests the contractor data has INCORRECT classification codes.\n";
echo "The codes should probably be:\n";
echo "  E-I-2iia → E-I-2a (Bridges - Construction)\n";
echo "  E-I-2iib → E-I-2b (Wharfs/Jettie - Construction)\n";
echo "  E-VI-5ia → E-VI-5a (Landscaping - Construction)\n";
echo "  E-VI-5iia → E-VI-5b (Landscaping - Maintenance)\n";
