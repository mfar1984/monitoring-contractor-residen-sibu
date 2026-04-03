<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ImportCompaniesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Starting import from pejabatresiden companies table...');
        
        try {
            // Connect to example database
            $exampleDB = DB::connection('pejabatresiden');
            
            // Get companies from example system
            $companies = $exampleDB->table('companies')->get();
            
            $this->command->info("Found {$companies->count()} companies in example system");
            
            $imported = 0;
            $skipped = 0;
            $errors = 0;
            
            foreach ($companies as $company) {
                try {
                    // Check if code already exists
                    $exists = DB::table('contractor_categories')
                        ->where('code', $company->registration_no) // Use registration_no as code
                        ->exists();
                    
                    if ($exists) {
                        $this->command->warn("Skipping company '{$company->name}' - code already exists");
                        $skipped++;
                        continue;
                    }
                    
                    // Map fields from example system to real system
                    $data = [
                        'company_name' => $company->name,
                        'code' => $company->registration_no ?? 'AUTO-' . uniqid(),
                        'registration_number' => $company->registration_no,
                        'office_registration_no' => $company->office_registration_no,
                        'email' => $company->email,
                        'telephone_no' => $company->telephone_no,
                        'mobile_no' => $company->mobile_no,
                        'fax_no' => $company->fax_no,
                        'contact_person' => $company->contact_person,
                        'contact_no' => $company->contact_no,
                        'date_established' => $company->date_established,
                        'company_type' => $company->company_type ?? 'contractor',
                        'company_category' => $company->company_category,
                        'registration_category' => $company->registration_category,
                        'division' => $company->division,
                        'district' => $company->district,
                        'registration_status_valid' => $company->registration_status_valid ?? 0,
                        'registration_status_expired' => $company->registration_status_expired ?? 0,
                        'bumiputera_status' => $company->bumiputera_status,
                        'rescue_contractor' => $company->rescue_contractor ?? 0,
                        'authorized_person_name' => $company->authorized_person_name,
                        'authorized_person_ic' => $company->authorized_person_ic,
                        'upk_license_no' => $company->upk_license_no,
                        'upk_expiry_date' => $company->upk_expiry_date,
                        'upkj_class' => $company->upkj_class,
                        'upkj_head' => $company->upkj_head,
                        'upkj_subhead' => $company->upkj_subhead,
                        'shareholders_data' => $company->shareholders_data,
                        'manpower_sole_proprietor' => $company->manpower_sole_proprietor ?? 0,
                        'manpower_management' => $company->manpower_management ?? 0,
                        'manpower_professional' => $company->manpower_professional ?? 0,
                        'manpower_sub_professional' => $company->manpower_sub_professional ?? 0,
                        'manpower_competent_worker' => $company->manpower_competent_worker ?? 0,
                        'manpower_total' => $company->manpower_total ?? 0,
                        'registered_address' => $company->registered_address,
                        'postal_address' => $company->postal_address,
                        'business_address' => $company->business_address,
                        'registered_location' => $company->registered_location,
                        'registered_address_city' => $company->registered_address_city,
                        'registered_address_state' => $company->registered_address_state,
                        'registered_address_postcode' => $company->registered_address_postcode,
                        'description' => null,
                        'status' => $this->mapStatus($company->status),
                        'created_at' => $company->created_at,
                        'updated_at' => $company->updated_at,
                    ];
                    
                    // Insert
                    DB::table('contractor_categories')->insert($data);
                    $this->command->info("✓ Imported: {$company->name}");
                    $imported++;
                    
                } catch (\Exception $e) {
                    $this->command->error("✗ Error importing '{$company->name}': " . $e->getMessage());
                    Log::error("Error importing company: {$company->name}", ['error' => $e->getMessage()]);
                    $errors++;
                }
            }
            
            $this->command->newLine();
            $this->command->info("=== Import Summary ===");
            $this->command->info("Total companies in example system: {$companies->count()}");
            $this->command->info("Successfully imported: {$imported}");
            $this->command->warn("Skipped (already exists): {$skipped}");
            $this->command->error("Errors: {$errors}");
            
        } catch (\Exception $e) {
            $this->command->error("Failed to connect to pejabatresiden database: " . $e->getMessage());
            Log::error("Failed to import companies", ['error' => $e->getMessage()]);
        }
    }
    
    /**
     * Map status from example system to real system
     */
    private function mapStatus($status): string
    {
        $statusMap = [
            'active' => 'Active',
            'inactive' => 'Inactive',
            'pending' => 'Pending',
            'rejected' => 'Rejected',
            'suspended' => 'Suspended',
        ];
        
        return $statusMap[strtolower($status)] ?? 'Active';
    }
}
