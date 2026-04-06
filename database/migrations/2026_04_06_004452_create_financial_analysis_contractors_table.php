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
        Schema::create('financial_analysis_contractors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('financial_analysis_id')
                ->constrained('financial_analyses')
                ->onDelete('cascade');
            $table->foreignId('contractor_category_id')
                ->constrained('contractor_categories')
                ->onDelete('restrict');
            
            // Basic Info (auto-populated)
            $table->string('contractor_name')->nullable();
            $table->string('registration_number', 100)->nullable();
            $table->string('contractor_class', 50)->nullable()->comment('E, EX, F&E, E&D');
            
            // Registration
            $table->date('registration_validity_date')->nullable();
            
            // UPKJ Classification (auto-populated from contractor records)
            $table->text('upkj_classifications')->nullable()->comment('Stored as comma-separated');
            
            // Current Workload
            $table->decimal('current_contract_load', 15, 2)->nullable();
            
            // Performance Record
            $table->text('performance_record')->nullable();
            
            // Financial Capacity
            $table->decimal('minimum_capital_requirement', 15, 2)->nullable();
            
            // Additional Financial
            $table->decimal('fixed_deposit', 15, 2)->nullable();
            $table->decimal('credit_facility_balance', 15, 2)->nullable();
            $table->decimal('additional_credit_facility', 15, 2)->nullable();
            
            // Calculated field (average of last 3 months)
            $table->decimal('three_month_average', 15, 2)->nullable();
            
            // Decision
            $table->text('meeting_decision')->nullable();
            $table->text('justification')->nullable();
            $table->boolean('is_qualified')->nullable()->comment('true = qualified, false = not qualified');
            $table->text('remarks')->nullable();
            
            // Display order
            $table->integer('display_order')->default(0);
            
            $table->timestamps();
            
            // Indexes
            $table->index('financial_analysis_id', 'idx_analysis');
            $table->index('contractor_category_id', 'idx_contractor');
            $table->index('display_order', 'idx_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('financial_analysis_contractors');
    }
};
