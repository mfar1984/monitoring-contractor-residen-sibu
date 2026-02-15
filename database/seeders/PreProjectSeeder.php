<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PreProject;

class PreProjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get IDs from existing master data
        $residenSibu = \App\Models\ResidenCategory::where('name', 'Residen Sibu')->first();
        $jkr = \App\Models\AgencyCategory::where('code', 'JKR')->first();
        $did = \App\Models\AgencyCategory::where('code', 'DID')->first();
        $jbab = \App\Models\AgencyCategory::where('code', 'JBAB')->first();
        $parliamentSibu = \App\Models\Parliament::where('name', 'Parlimen Sibu')->first();
        $parliamentLanang = \App\Models\Parliament::where('name', 'Parlimen Lanang')->first();
        $divisionSibu = \App\Models\Division::where('name', 'BAHAGIAN SIBU')->first();
        $districtSibu = \App\Models\District::where('name', 'Sibu')->first();
        $districtSelangau = \App\Models\District::where('name', 'Selangau')->first();
        
        // Get DUNs
        $duns = \App\Models\Dun::all();
        $dunNangka = $duns->first();
        
        // Get Land Title Status
        $landTitleGov = \App\Models\LandTitleStatus::where('code', 'LTS-GOV')->first();
        if (!$landTitleGov) {
            $landTitleGov = \App\Models\LandTitleStatus::first();
        }
        
        // Get Implementation Methods
        $methodTender = \App\Models\ImplementationMethod::where('code', 'IM-TENDER')->first();
        $methodDirect = \App\Models\ImplementationMethod::where('code', 'IM-DIRECT')->first();
        
        // Get Project Ownerships
        $ownershipState = \App\Models\ProjectOwnership::where('code', 'PO-STATE')->first();
        
        // Get Project Categories
        $projectCategories = \App\Models\ProjectCategory::all();
        
        $preProjects = [
            [
                'name' => 'Sibu Town Road Upgrading Project',
                'residen_category_id' => $residenSibu?->id,
                'agency_category_id' => $jkr?->id,
                'parliament_id' => $parliamentSibu?->id,
                'project_category_id' => $projectCategories->first()?->id,
                'project_scope' => 'Upgrading of 5km town roads including drainage system and street lighting',
                'actual_project_cost' => 2500000.00,
                'consultation_cost' => 150000.00,
                'lss_inspection_cost' => 50000.00,
                'sst' => 150000.00,
                'others_cost' => 50000.00,
                'total_cost' => 2900000.00,
                'implementation_period' => 'Jan 2026 - Dec 2026',
                'division_id' => $divisionSibu?->id,
                'district_id' => $districtSibu?->id,
                'parliament_location_id' => $parliamentSibu?->id,
                'dun_id' => $dunNangka?->id,
                'site_layout' => 'Yes',
                'land_title_status_id' => $landTitleGov?->id,
                'consultation_service' => 'Yes',
                'implementing_agency_id' => $jkr?->id,
                'implementation_method_id' => $methodTender?->id,
                'project_ownership_id' => $ownershipState?->id,
                'jkkk_name' => 'JKKK Nangka',
                'state_government_asset' => 'Yes',
                'bill_of_quantity' => 'Yes',
                'status' => 'Active',
            ],
            [
                'name' => 'Bawang Assan Water Supply Expansion',
                'residen_category_id' => $residenSibu?->id,
                'agency_category_id' => $jbab?->id,
                'parliament_id' => $parliamentSibu?->id,
                'project_category_id' => $projectCategories->skip(1)->first()?->id,
                'project_scope' => 'Expansion of water treatment plant and distribution network to serve 500 additional households',
                'actual_project_cost' => 1800000.00,
                'consultation_cost' => 100000.00,
                'lss_inspection_cost' => 30000.00,
                'sst' => 108000.00,
                'others_cost' => 20000.00,
                'total_cost' => 2058000.00,
                'implementation_period' => 'Mar 2026 - Sep 2026',
                'division_id' => $divisionSibu?->id,
                'district_id' => $districtSibu?->id,
                'parliament_location_id' => $parliamentSibu?->id,
                'dun_id' => $duns->skip(1)->first()?->id,
                'site_layout' => 'Yes',
                'land_title_status_id' => $landTitleGov?->id,
                'consultation_service' => 'Yes',
                'implementing_agency_id' => $jbab?->id,
                'implementation_method_id' => $methodDirect?->id,
                'project_ownership_id' => $ownershipState?->id,
                'jkkk_name' => 'JKKK Bawang Assan',
                'state_government_asset' => 'Yes',
                'bill_of_quantity' => 'Yes',
                'status' => 'Active',
            ],
            [
                'name' => 'Lanang Bridge Maintenance Project',
                'residen_category_id' => $residenSibu?->id,
                'agency_category_id' => $jkr?->id,
                'parliament_id' => $parliamentLanang?->id,
                'project_category_id' => $projectCategories->first()?->id,
                'project_scope' => 'Major maintenance and repair of existing bridge structure including repainting and structural reinforcement',
                'actual_project_cost' => 650000.00,
                'consultation_cost' => 40000.00,
                'lss_inspection_cost' => 15000.00,
                'sst' => 39000.00,
                'others_cost' => 8000.00,
                'total_cost' => 752000.00,
                'implementation_period' => 'May 2026 - Aug 2026',
                'division_id' => $divisionSibu?->id,
                'district_id' => $districtSelangau?->id,
                'parliament_location_id' => $parliamentLanang?->id,
                'dun_id' => $duns->skip(2)->first()?->id,
                'site_layout' => 'Yes',
                'land_title_status_id' => $landTitleGov?->id,
                'consultation_service' => 'Yes',
                'implementing_agency_id' => $jkr?->id,
                'implementation_method_id' => $methodDirect?->id,
                'project_ownership_id' => $ownershipState?->id,
                'jkkk_name' => 'JKKK Pelawan',
                'state_government_asset' => 'Yes',
                'bill_of_quantity' => 'No',
                'status' => 'Active',
            ],
        ];

        foreach ($preProjects as $project) {
            PreProject::create($project);
        }
    }
}
