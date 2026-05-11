<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\UpkjClassification;

echo "=== Checking Class E Head IV ===\n\n";

// Get all Class E Head IV classifications
$classE_IV = UpkjClassification::where('class', 'E')
    ->where('head_code', 'IV')
    ->orderBy('subhead_code')
    ->orderBy('subhead_roman')
    ->orderBy('subhead_letter')
    ->get();

echo "Class E = Works - Above 200,000 To 1,000,000\n";
echo "Head IV = Works - Irrigation And Drainage Works\n\n";

echo "Total classifications: {$classE_IV->count()}\n\n";

if ($classE_IV->count() > 0) {
    echo "All E-IV classifications:\n\n";
    
    foreach ($classE_IV as $c) {
        $code = "E-IV-{$c->subhead_code}";
        if ($c->subhead_roman) {
            $code .= $c->subhead_roman;
        }
        if ($c->subhead_letter) {
            $code .= $c->subhead_letter;
        }
        
        echo "  $code: {$c->description}\n";
    }
} else {
    echo "❌ Class E Head IV TIDAK ADA dalam master data!\n";
}

echo "\n\n=== Checking Class F Head IV ===\n\n";

// Get all Class F Head IV classifications
$classF_IV = UpkjClassification::where('class', 'F')
    ->where('head_code', 'IV')
    ->orderBy('subhead_code')
    ->orderBy('subhead_roman')
    ->orderBy('subhead_letter')
    ->get();

echo "Class F = Works - 200,000 And Below\n";
echo "Head IV = Works - Irrigation And Drainage Works\n\n";

echo "Total classifications: {$classF_IV->count()}\n\n";

if ($classF_IV->count() > 0) {
    echo "All F-IV classifications:\n\n";
    
    foreach ($classF_IV as $c) {
        $code = "F-IV-{$c->subhead_code}";
        if ($c->subhead_roman) {
            $code .= $c->subhead_roman;
        }
        if ($c->subhead_letter) {
            $code .= $c->subhead_letter;
        }
        
        echo "  $code: {$c->description}\n";
    }
} else {
    echo "❌ Class F Head IV TIDAK ADA dalam master data!\n";
}

echo "\n\n=== Checking Class D Head IV ===\n\n";

// Get all Class D Head IV classifications
$classD_IV = UpkjClassification::where('class', 'D')
    ->where('head_code', 'IV')
    ->orderBy('subhead_code')
    ->orderBy('subhead_roman')
    ->orderBy('subhead_letter')
    ->get();

echo "Class D = Works - Above 1,000,000 To 3,000,000\n";
echo "Head IV = Works - Irrigation And Drainage Works\n\n";

echo "Total classifications: {$classD_IV->count()}\n\n";

if ($classD_IV->count() > 0) {
    echo "All D-IV classifications:\n\n";
    
    foreach ($classD_IV as $c) {
        $code = "D-IV-{$c->subhead_code}";
        if ($c->subhead_roman) {
            $code .= $c->subhead_roman;
        }
        if ($c->subhead_letter) {
            $code .= $c->subhead_letter;
        }
        
        echo "  $code: {$c->description}\n";
    }
} else {
    echo "❌ Class D Head IV TIDAK ADA dalam master data!\n";
}
