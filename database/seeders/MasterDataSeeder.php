<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ResidenCategory;
use App\Models\AgencyCategory;
use App\Models\ProjectCategory;
use App\Models\Division;
use App\Models\District;
use App\Models\Parliament;
use App\Models\Dun;
use App\Models\LandTitleStatus;
use App\Models\ProjectOwnership;
use App\Models\ImplementationMethod;

class MasterDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Residen Categories
        $residenCategories = [
            ['name' => 'Residen Sibu', 'code' => 'RES-SBU', 'description' => 'Residen Office Sibu', 'status' => 'Active'],
            ['name' => 'Residen Kapit', 'code' => 'RES-KPT', 'description' => 'Residen Office Kapit', 'status' => 'Active'],
            ['name' => 'Residen Mukah', 'code' => 'RES-MKH', 'description' => 'Residen Office Mukah', 'status' => 'Active'],
        ];

        foreach ($residenCategories as $category) {
            ResidenCategory::create($category);
        }

        // Agency Categories
        $agencyCategories = [
            ['name' => 'DID (Department of Irrigation and Drainage)', 'code' => 'DID', 'description' => 'Drainage and irrigation projects', 'status' => 'Active'],
            ['name' => 'JKR (Public Works Department)', 'code' => 'JKR', 'description' => 'Road and infrastructure projects', 'status' => 'Active'],
            ['name' => 'JBAB (Sarawak Water Board)', 'code' => 'JBAB', 'description' => 'Water supply projects', 'status' => 'Active'],
            ['name' => 'JPJ (Road Transport Department)', 'code' => 'JPJ', 'description' => 'Transport related projects', 'status' => 'Active'],
        ];

        foreach ($agencyCategories as $category) {
            AgencyCategory::create($category);
        }

        // Project Categories
        $projectCategories = [
            ['name' => 'Infrastructure Development', 'code' => 'INFRA', 'description' => 'Roads, bridges, buildings', 'status' => 'Active'],
            ['name' => 'Water Supply', 'code' => 'WATER', 'description' => 'Water treatment and distribution', 'status' => 'Active'],
            ['name' => 'Drainage System', 'code' => 'DRAIN', 'description' => 'Drainage and flood control', 'status' => 'Active'],
            ['name' => 'Community Facilities', 'code' => 'COMM', 'description' => 'Community halls, sports facilities', 'status' => 'Active'],
        ];

        foreach ($projectCategories as $category) {
            ProjectCategory::create($category);
        }

        // Divisions
        $divisions = [
            ['name' => 'Sibu Division', 'code' => 'DIV-SBU', 'description' => 'Sibu Division', 'status' => 'Active'],
            ['name' => 'Kapit Division', 'code' => 'DIV-KPT', 'description' => 'Kapit Division', 'status' => 'Active'],
            ['name' => 'Mukah Division', 'code' => 'DIV-MKH', 'description' => 'Mukah Division', 'status' => 'Active'],
        ];

        foreach ($divisions as $division) {
            Division::create($division);
        }

        // Districts
        $districts = [
            ['division_id' => 1, 'name' => 'Sibu', 'code' => 'DIST-SBU', 'description' => 'Sibu District', 'status' => 'Active'],
            ['division_id' => 1, 'name' => 'Kanowit', 'code' => 'DIST-KNW', 'description' => 'Kanowit District', 'status' => 'Active'],
            ['division_id' => 1, 'name' => 'Selangau', 'code' => 'DIST-SLG', 'description' => 'Selangau District', 'status' => 'Active'],
            ['division_id' => 2, 'name' => 'Kapit', 'code' => 'DIST-KPT', 'description' => 'Kapit District', 'status' => 'Active'],
            ['division_id' => 2, 'name' => 'Song', 'code' => 'DIST-SNG', 'description' => 'Song District', 'status' => 'Active'],
            ['division_id' => 3, 'name' => 'Mukah', 'code' => 'DIST-MKH', 'description' => 'Mukah District', 'status' => 'Active'],
            ['division_id' => 3, 'name' => 'Dalat', 'code' => 'DIST-DLT', 'description' => 'Dalat District', 'status' => 'Active'],
        ];

        foreach ($districts as $district) {
            District::create($district);
        }

        // Parliaments
        $parliaments = [
            ['name' => 'Parlimen Sibu', 'code' => 'P-SBU', 'budget' => 5000000, 'description' => 'Sibu Parliamentary Constituency', 'status' => 'Active'],
            ['name' => 'Parlimen Lanang', 'code' => 'P-LNG', 'budget' => 4500000, 'description' => 'Lanang Parliamentary Constituency', 'status' => 'Active'],
            ['name' => 'Parlimen Kapit', 'code' => 'P-KPT', 'budget' => 4000000, 'description' => 'Kapit Parliamentary Constituency', 'status' => 'Active'],
            ['name' => 'Parlimen Mukah', 'code' => 'P-MKH', 'budget' => 3500000, 'description' => 'Mukah Parliamentary Constituency', 'status' => 'Active'],
        ];

        foreach ($parliaments as $parliament) {
            Parliament::create($parliament);
        }

        // DUNs
        $duns = [
            ['name' => 'DUN Nangka', 'code' => 'DUN-NGK', 'budget' => 1500000, 'description' => 'Nangka State Constituency', 'status' => 'Active'],
            ['name' => 'DUN Bawang Assan', 'code' => 'DUN-BWA', 'budget' => 1500000, 'description' => 'Bawang Assan State Constituency', 'status' => 'Active'],
            ['name' => 'DUN Pelawan', 'code' => 'DUN-PLW', 'budget' => 1400000, 'description' => 'Pelawan State Constituency', 'status' => 'Active'],
            ['name' => 'DUN Dudong', 'code' => 'DUN-DDG', 'budget' => 1600000, 'description' => 'Dudong State Constituency', 'status' => 'Active'],
            ['name' => 'DUN Bukit Assek', 'code' => 'DUN-BAS', 'budget' => 1700000, 'description' => 'Bukit Assek State Constituency', 'status' => 'Active'],
            ['name' => 'DUN Pelagus', 'code' => 'DUN-PLG', 'budget' => 1300000, 'description' => 'Pelagus State Constituency', 'status' => 'Active'],
            ['name' => 'DUN Katibas', 'code' => 'DUN-KTB', 'budget' => 1200000, 'description' => 'Katibas State Constituency', 'status' => 'Active'],
            ['name' => 'DUN Balingian', 'code' => 'DUN-BLG', 'budget' => 1100000, 'description' => 'Balingian State Constituency', 'status' => 'Active'],
        ];

        foreach ($duns as $dun) {
            Dun::create($dun);
        }

        // Land Title Statuses
        $landTitleStatuses = [
            ['name' => 'Freehold', 'code' => 'LTS-FH', 'description' => 'Freehold land title', 'status' => 'Active'],
            ['name' => 'Leasehold', 'code' => 'LTS-LH', 'description' => 'Leasehold land title', 'status' => 'Active'],
            ['name' => 'Native Customary Rights (NCR)', 'code' => 'LTS-NCR', 'description' => 'Native customary rights land', 'status' => 'Active'],
            ['name' => 'Government Land', 'code' => 'LTS-GOV', 'description' => 'Government owned land', 'status' => 'Active'],
            ['name' => 'Temporary Occupation License (TOL)', 'code' => 'LTS-TOL', 'description' => 'Temporary occupation license', 'status' => 'Active'],
        ];

        foreach ($landTitleStatuses as $status) {
            LandTitleStatus::create($status);
        }

        // Project Ownerships
        $projectOwnerships = [
            ['name' => 'State Government', 'code' => 'PO-STATE', 'description' => 'Owned by State Government', 'status' => 'Active'],
            ['name' => 'Federal Government', 'code' => 'PO-FED', 'description' => 'Owned by Federal Government', 'status' => 'Active'],
            ['name' => 'Local Authority', 'code' => 'PO-LOCAL', 'description' => 'Owned by Local Authority', 'status' => 'Active'],
            ['name' => 'Private', 'code' => 'PO-PRIV', 'description' => 'Privately owned', 'status' => 'Active'],
            ['name' => 'Community', 'code' => 'PO-COMM', 'description' => 'Community owned', 'status' => 'Active'],
        ];

        foreach ($projectOwnerships as $ownership) {
            ProjectOwnership::create($ownership);
        }

        // Implementation Methods
        $implementationMethods = [
            ['name' => 'Direct Contract', 'code' => 'IM-DIRECT', 'description' => 'Direct contract with contractor', 'status' => 'Active'],
            ['name' => 'Open Tender', 'code' => 'IM-TENDER', 'description' => 'Open tender process', 'status' => 'Active'],
            ['name' => 'Quotation', 'code' => 'IM-QUOTE', 'description' => 'Quotation based selection', 'status' => 'Active'],
            ['name' => 'In-house', 'code' => 'IM-HOUSE', 'description' => 'In-house implementation', 'status' => 'Active'],
            ['name' => 'Public-Private Partnership (PPP)', 'code' => 'IM-PPP', 'description' => 'Public-private partnership', 'status' => 'Active'],
        ];

        foreach ($implementationMethods as $method) {
            ImplementationMethod::create($method);
        }
    }
}
