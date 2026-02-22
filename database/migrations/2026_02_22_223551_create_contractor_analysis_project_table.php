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
        Schema::create('contractor_analysis_project', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('contractor_analysis_transfer_id');
            $table->unsignedBigInteger('project_id');
            $table->timestamps();
            
            // Foreign keys with custom names to avoid length issues
            $table->foreign('contractor_analysis_transfer_id', 'fk_ca_transfer')
                ->references('id')
                ->on('contractor_analysis_transfers')
                ->onDelete('cascade');
            
            $table->foreign('project_id', 'fk_ca_project')
                ->references('id')
                ->on('projects')
                ->onDelete('cascade');
            
            // Index for faster queries
            $table->index(['contractor_analysis_transfer_id', 'project_id'], 'idx_transfer_project');
            
            // Ensure unique combination
            $table->unique(['contractor_analysis_transfer_id', 'project_id'], 'unique_transfer_project');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contractor_analysis_project');
    }
};
