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
        Schema::create('contractor_analysis_transfers', function (Blueprint $table) {
            $table->id();
            $table->string('transfer_number', 50)->unique();
            $table->foreignId('created_by')->constrained('users')->onDelete('restrict');
            $table->foreignId('agency_category_id')->constrained('agency_categories')->onDelete('restrict');
            $table->string('attachment_path', 255);
            $table->string('status', 50)->default('Draft');
            $table->timestamps();
            
            // Indexes for better query performance
            $table->index('transfer_number');
            $table->index('agency_category_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contractor_analysis_transfers');
    }
};
