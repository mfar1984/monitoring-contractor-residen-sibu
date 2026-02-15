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
        Schema::create('pre_projects', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            
            // Integrated with master data
            $table->foreignId('residen_category_id')->nullable()->constrained('residen_categories')->onDelete('set null');
            $table->foreignId('agency_category_id')->nullable()->constrained('agency_categories')->onDelete('set null');
            $table->foreignId('parliament_id')->nullable()->constrained('parliaments')->onDelete('set null');
            $table->foreignId('project_category_id')->nullable()->constrained('project_categories')->onDelete('set null');
            $table->text('project_scope')->nullable();
            
            // Cost of Project
            $table->decimal('actual_project_cost', 15, 2)->nullable();
            $table->decimal('consultation_cost', 15, 2)->nullable();
            $table->decimal('lss_inspection_cost', 15, 2)->nullable();
            $table->decimal('sst', 15, 2)->nullable();
            $table->decimal('others_cost', 15, 2)->nullable();
            $table->decimal('total_cost', 15, 2)->nullable();
            
            // Project Location
            $table->string('implementation_period')->nullable();
            $table->foreignId('division_id')->nullable()->constrained('divisions')->onDelete('set null');
            $table->foreignId('district_id')->nullable()->constrained('districts')->onDelete('set null');
            $table->foreignId('parliament_location_id')->nullable()->constrained('parliaments')->onDelete('set null');
            $table->foreignId('dun_id')->nullable()->constrained('duns')->onDelete('set null');
            
            // Site Information
            $table->enum('site_layout', ['Yes', 'No'])->nullable();
            $table->foreignId('land_title_status_id')->nullable()->constrained('land_title_statuses')->onDelete('set null');
            $table->enum('consultation_service', ['Yes', 'No'])->nullable();
            
            // Implementation Details
            $table->string('implementing_agency')->nullable();
            $table->string('implementation_method')->nullable();
            $table->foreignId('project_ownership_parliament_id')->nullable()->constrained('parliaments')->onDelete('set null');
            $table->string('jkkk_name')->nullable();
            $table->enum('state_government_asset', ['Yes', 'No'])->nullable();
            $table->enum('bill_of_quantity', ['Yes', 'No'])->nullable();
            
            $table->enum('status', ['Active', 'Inactive'])->default('Active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pre_projects');
    }
};
