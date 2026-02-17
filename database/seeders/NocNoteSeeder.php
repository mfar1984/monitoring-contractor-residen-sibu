<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\NocNote;

class NocNoteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $nocNotes = [
            ['name' => 'Change of Project Scope', 'code' => 'NOC-SCOPE', 'description' => 'Project scope has been modified', 'status' => 'Active'],
            ['name' => 'Budget Adjustment', 'code' => 'NOC-BUDGET', 'description' => 'Project budget has been adjusted', 'status' => 'Active'],
            ['name' => 'Timeline Extension', 'code' => 'NOC-TIME', 'description' => 'Project timeline has been extended', 'status' => 'Active'],
            ['name' => 'Location Change', 'code' => 'NOC-LOC', 'description' => 'Project location has been changed', 'status' => 'Active'],
            ['name' => 'Agency Transfer', 'code' => 'NOC-AGENCY', 'description' => 'Implementing agency has been changed', 'status' => 'Active'],
            ['name' => 'Design Modification', 'code' => 'NOC-DESIGN', 'description' => 'Project design has been modified', 'status' => 'Active'],
            ['name' => 'Cost Revision', 'code' => 'NOC-COST', 'description' => 'Project cost has been revised', 'status' => 'Active'],
        ];

        foreach ($nocNotes as $note) {
            NocNote::create($note);
        }
    }
}
