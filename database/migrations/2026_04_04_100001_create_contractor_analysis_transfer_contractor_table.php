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
        Schema::create('contractor_analysis_transfer_contractor', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('contractor_analysis_transfer_id');
            $table->unsignedBigInteger('contractor_category_id');
            $table->timestamps();
            
            // Foreign keys with custom names to avoid length issues
            $table->foreign('contractor_analysis_transfer_id', 'fk_catc_transfer')
                ->references('id')
                ->on('contractor_analysis_transfers')
                ->onDelete('cascade');
            
            $table->foreign('contractor_category_id', 'fk_catc_contractor')
                ->references('id')
                ->on('contractor_categories')
                ->onDelete('cascade');
            
            // Unique constraint to prevent duplicate contractor assignments
            $table->unique(
                ['contractor_analysis_transfer_id', 'contractor_category_id'],
                'unique_transfer_contractor'
            );
            
            // Indexes for performance
            $table->index('contractor_analysis_transfer_id', 'idx_catc_transfer');
            $table->index('contractor_category_id', 'idx_catc_contractor');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contractor_analysis_transfer_contractor');
    }
};
