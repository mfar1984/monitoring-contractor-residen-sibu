<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Rename table from noc_pre_project to noc_project
        Schema::rename('noc_pre_project', 'noc_project');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Rename table back from noc_project to noc_pre_project
        Schema::rename('noc_project', 'noc_pre_project');
    }
};
