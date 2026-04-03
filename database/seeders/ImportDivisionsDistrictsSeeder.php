<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Division;
use App\Models\District;

class ImportDivisionsDistrictsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        echo "Importing Divisions and Districts from pejabatresiden system...\n\n";
        
        // Get all categories from example system
        $categories = DB::connection('pejabatresiden')
            ->table('categories')
            ->select('id', 'name', 'parent_id')
            ->get();
        
        // Separate divisions (parent_id = NULL) and districts (parent_id != NULL)
        $divisions = $categories->whereNull('parent_id');
        $districts = $categories->whereNotNull('parent_id');
        
        echo "Found {$divisions->count()} divisions and {$districts->count()} districts in example system\n\n";
        
        // Import Divisions
        echo "Importing Divisions...\n";
        $divisionMapping = []; // Map old ID to new ID
        
        foreach ($divisions as $division) {
            // Generate unique code
            $baseCode = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $division->name), 0, 6));
            $code = $baseCode;
            $counter = 1;
            
            // Check if code exists, if yes, append number
            while (Division::where('code', $code)->exists()) {
                $code = $baseCode . $counter;
                $counter++;
            }
            
            $newDivision = Division::firstOrCreate(
                ['name' => $division->name],
                [
                    'code' => $code,
                    'description' => $division->name,
                    'status' => 'Active'
                ]
            );
            
            $divisionMapping[$division->id] = $newDivision->id;
            echo "  ✓ {$division->name} (Code: {$code}, Old ID: {$division->id} → New ID: {$newDivision->id})\n";
        }
        
        echo "\n";
        
        // Import Districts
        echo "Importing Districts...\n";
        foreach ($districts as $district) {
            // Find the new division_id based on parent_id mapping
            $newDivisionId = $divisionMapping[$district->parent_id] ?? null;
            
            if (!$newDivisionId) {
                echo "  ✗ Skipping {$district->name} - Division not found (Parent ID: {$district->parent_id})\n";
                continue;
            }
            
            // Generate unique code
            $baseCode = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $district->name), 0, 6));
            $code = $baseCode;
            $counter = 1;
            
            // Check if code exists, if yes, append number
            while (District::where('code', $code)->exists()) {
                $code = $baseCode . $counter;
                $counter++;
            }
            
            $newDistrict = District::firstOrCreate(
                ['name' => $district->name, 'division_id' => $newDivisionId],
                [
                    'code' => $code,
                    'description' => $district->name,
                    'status' => 'Active'
                ]
            );
            
            echo "  ✓ {$district->name} → Division ID: {$newDivisionId} (Code: {$code}, District ID: {$newDistrict->id})\n";
        }
        
        echo "\n";
        echo "Import completed!\n";
        echo "Total Divisions: " . Division::count() . "\n";
        echo "Total Districts: " . District::count() . "\n";
    }
}
