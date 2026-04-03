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
        Schema::create('contractor_upkj_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contractor_category_id')
                  ->constrained('contractor_categories')
                  ->onDelete('cascade');
            $table->string('category', 50); // Works, Supplies & Services, Electrical, Mechanical
            $table->string('registration_status', 20); // Valid, Expired, Pending
            $table->string('validity_period', 100)->nullable(); // Free text date range
            $table->string('bumiputera_status', 10)->nullable(); // Yes, No
            $table->string('bumiputera_validity', 100)->nullable(); // Free text date range
            $table->string('certificate_no', 100)->nullable();
            $table->json('classifications'); // Array of classification objects
            $table->timestamps();
            
            // Indexes for better query performance
            $table->index('contractor_category_id');
            $table->index('category');
            $table->index('registration_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contractor_upkj_records');
    }
};
