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
        Schema::table('noc_pre_project', function (Blueprint $table) {
            // Drop the foreign key first
            $table->dropForeign(['pre_project_id']);
            
            // Make pre_project_id nullable
            $table->foreignId('pre_project_id')->nullable()->change();
            
            // Re-add the foreign key constraint
            $table->foreign('pre_project_id')->references('id')->on('pre_projects')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('noc_pre_project', function (Blueprint $table) {
            // Drop the foreign key
            $table->dropForeign(['pre_project_id']);
            
            // Make pre_project_id not nullable
            $table->foreignId('pre_project_id')->nullable(false)->change();
            
            // Re-add the foreign key constraint
            $table->foreign('pre_project_id')->references('id')->on('pre_projects')->onDelete('cascade');
        });
    }
};
