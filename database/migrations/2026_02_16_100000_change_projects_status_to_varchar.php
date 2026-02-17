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
        Schema::table('projects', function (Blueprint $table) {
            // Change status from ENUM to VARCHAR to support dynamic NOC note names
            $table->string('status', 100)->default('Active')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            // Revert back to ENUM
            $table->enum('status', ['Active', 'NOC', 'Cancelled'])->default('Active')->change();
        });
    }
};
