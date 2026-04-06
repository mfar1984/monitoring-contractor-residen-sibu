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
        Schema::create('financial_analysis_bank_statements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('financial_analysis_contractor_id');
            $table->foreign('financial_analysis_contractor_id', 'fk_bank_stmt_contractor')
                ->references('id')
                ->on('financial_analysis_contractors')
                ->onDelete('cascade');
            
            // Month identification
            $table->string('month_year', 20)->comment('e.g., Sep-23, Oct-23');
            $table->date('month_date')->comment('Actual date for sorting (e.g., 2023-09-01)');
            
            // Bank statement data
            $table->decimal('ending_balance', 15, 2)->nullable()->comment('Can be negative for overdraft');
            
            // Display order
            $table->integer('display_order')->default(0);
            
            $table->timestamps();
            
            // Indexes
            $table->index('financial_analysis_contractor_id', 'idx_contractor');
            $table->index('month_date', 'idx_month');
            $table->index('display_order', 'idx_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('financial_analysis_bank_statements');
    }
};
