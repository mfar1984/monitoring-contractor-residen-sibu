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
        Schema::create('financial_analysis_approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('financial_analysis_id')
                ->constrained('financial_analyses')
                ->onDelete('cascade');
            
            // Committee member details
            $table->string('position')->comment('e.g., Residen Bahagian Sibu');
            $table->string('name')->nullable();
            $table->string('department')->nullable();
            $table->string('signature_path')->nullable()->comment('Digital signature file path');
            $table->timestamp('signed_at')->nullable();
            
            // Display order
            $table->integer('display_order')->default(0);
            
            $table->timestamps();
            
            // Indexes
            $table->index('financial_analysis_id', 'idx_analysis');
            $table->index('display_order', 'idx_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('financial_analysis_approvals');
    }
};
