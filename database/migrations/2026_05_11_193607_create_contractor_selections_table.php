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
        Schema::create('contractor_selections', function (Blueprint $table) {
            $table->id();
            $table->string('selection_number')->unique(); // SEL/YYYY/###
            
            // Filter criteria
            $table->foreignId('division_id')->nullable()->constrained('divisions')->onDelete('set null');
            $table->foreignId('district_id')->nullable()->constrained('districts')->onDelete('set null');
            $table->json('upkj_categories')->nullable(); // Array of selected categories
            $table->json('upkj_classes')->nullable(); // Array of selected classes
            $table->json('upkj_heads')->nullable(); // Array of selected heads
            $table->json('upkj_subheads')->nullable(); // Array of selected subheads
            
            // Metadata
            $table->foreignId('created_by')->constrained('users')->onDelete('restrict');
            $table->string('status')->default('Active'); // Active, Archived
            $table->timestamp('generated_at'); // Fingerprint timestamp
            
            $table->timestamps();
            
            // Indexes
            $table->index('selection_number');
            $table->index('division_id');
            $table->index('district_id');
            $table->index('created_by');
            $table->index('status');
            $table->index('generated_at');
        });
        
        // Pivot table for contractor selection (fingerprint data)
        Schema::create('contractor_selection_contractor', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contractor_selection_id')->constrained('contractor_selections')->onDelete('cascade');
            $table->foreignId('contractor_category_id')->constrained('contractor_categories')->onDelete('cascade');
            
            // Fingerprint data (snapshot at time of generation)
            $table->string('company_name');
            $table->string('registration_number')->nullable();
            $table->string('upkj_class')->nullable();
            $table->string('upkj_head')->nullable();
            $table->string('upkj_subhead')->nullable();
            $table->date('upk_expiry_date')->nullable();
            $table->string('status_at_generation'); // Status at time of generation
            $table->json('snapshot_data'); // Full contractor data snapshot
            
            $table->timestamps();
            
            // Indexes
            $table->index(['contractor_selection_id', 'contractor_category_id'], 'selection_contractor_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contractor_selection_contractor');
        Schema::dropIfExists('contractor_selections');
    }
};
