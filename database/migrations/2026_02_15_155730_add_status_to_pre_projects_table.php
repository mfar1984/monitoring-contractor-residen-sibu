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
        Schema::table('pre_projects', function (Blueprint $table) {
            // Change status column from enum('Active', 'Inactive') to enum('Active', 'Inactive', 'NOC')
            $table->enum('status', ['Active', 'Inactive', 'NOC'])->default('Active')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pre_projects', function (Blueprint $table) {
            // Revert back to original enum
            $table->enum('status', ['Active', 'Inactive'])->default('Active')->change();
        });
    }
};
