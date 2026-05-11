<?php

echo "=== Testing Regex Fix ===\n\n";

$testCodes = [
    '1' => ['code' => '1', 'roman' => null, 'letter' => null],
    '2a' => ['code' => '2', 'roman' => null, 'letter' => 'a'],
    '2ia' => ['code' => '2', 'roman' => 'i', 'letter' => 'a'],
    '2iia' => ['code' => '2', 'roman' => 'ii', 'letter' => 'a'],
    '2iib' => ['code' => '2', 'roman' => 'ii', 'letter' => 'b'],
    '2iic' => ['code' => '2', 'roman' => 'ii', 'letter' => 'c'],
    '3iia' => ['code' => '3', 'roman' => 'ii', 'letter' => 'a'],
    '5ia' => ['code' => '5', 'roman' => 'i', 'letter' => 'a'],
    '5iia' => ['code' => '5', 'roman' => 'ii', 'letter' => 'a'],
    '5b' => ['code' => '5', 'roman' => null, 'letter' => 'b'],
];

echo "Testing CORRECT regex pattern: /^(\\d+)([ivx]*)([a-z]*)$/i\n";
echo "(Format: number + roman + letter)\n\n";

foreach ($testCodes as $input => $expected) {
    preg_match('/^(\d+)([ivx]*)([a-z]*)$/i', $input, $matches);
    
    $subheadCode = $matches[1] ?? '';
    $subheadRoman = $matches[2] ?: null;
    $subheadLetter = $matches[3] ?: null;
    
    $pass = ($subheadCode == $expected['code'] && 
             $subheadRoman == $expected['roman'] && 
             $subheadLetter == $expected['letter']);
    
    $status = $pass ? '✅ PASS' : '❌ FAIL';
    
    echo "$status | Input: $input\n";
    echo "  Expected: code={$expected['code']}, roman={$expected['roman']}, letter={$expected['letter']}\n";
    echo "  Got:      code=$subheadCode, roman=$subheadRoman, letter=$subheadLetter\n";
    echo "\n";
}

echo "\n=== Testing OLD (WRONG) regex pattern ===\n";
echo "Pattern: /^(\\d+)([a-z]*)([ivx]*)$/i\n";
echo "(Format: number + letter + roman - WRONG ORDER!)\n\n";

foreach (['2iia', '5ia', '3iia'] as $input) {
    preg_match('/^(\d+)([a-z]*)([ivx]*)$/i', $input, $matches);
    
    $subheadCode = $matches[1] ?? '';
    $subheadLetter = $matches[2] ?? null;
    $subheadRoman = $matches[3] ?? null;
    
    echo "Input: $input\n";
    echo "  Parsed as: code=$subheadCode, letter=$subheadLetter, roman=$subheadRoman\n";
    echo "  ❌ WRONG! Letter='$subheadLetter' should be roman numeral!\n";
    echo "\n";
}
