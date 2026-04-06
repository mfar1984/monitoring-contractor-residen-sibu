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
        Schema::create('financial_analyses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contractor_analysis_transfer_id')
                ->constrained('contractor_analysis_transfers')
                ->onDelete('cascade');
            
            // Project Information (auto-populated from transfer)
            $table->string('project_number', 100)->nullable();
            $table->text('project_name')->nullable();
            $table->string('agency_name')->nullable();
            $table->decimal('department_budget', 15, 2)->nullable();
            $table->string('district_name')->nullable();
            $table->string('project_class')->nullable();
            
            // Status and tracking
            $table->enum('status', ['Draft', 'Submitted', 'Approved', 'Rejected'])->default('Draft');
            $table->foreignId('created_by')->constrained('users')->onDelete('restrict');
            $table->timestamp('submitted_at')->nullable();
            $table->foreignId('submitted_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('rejected_at')->nullable();
            $table->foreignId('rejected_by')->nullable()->constrained('users')->onDelete('set null');
            $table->text('rejection_remarks')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index('contractor_analysis_transfer_id', 'idx_transfer');
            $table->index('status', 'idx_status');
            $table->index('created_by', 'idx_created_by');
            
            // Unique constraint - one analysis per transfer
            $table->unique('contractor_analysis_transfer_id', 'unique_transfer_analysis');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('financial_analyses');
    }
};
