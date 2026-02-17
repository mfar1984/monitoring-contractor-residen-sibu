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
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            
            // Project identification
            $table->string('project_number')->unique();
            $table->unsignedBigInteger('pre_project_id');
            $table->timestamp('approval_date');
            $table->timestamp('transferred_at');
            
            // All fields from pre_projects table
            $table->string('name');
            $table->unsignedBigInteger('residen_category_id')->nullable();
            $table->unsignedBigInteger('agency_category_id')->nullable();
            $table->unsignedBigInteger('parliament_id')->nullable();
            $table->unsignedBigInteger('dun_basic_id')->nullable();
            $table->unsignedBigInteger('project_category_id')->nullable();
            $table->text('project_scope')->nullable();
            $table->decimal('actual_project_cost', 15, 2)->nullable();
            $table->decimal('consultation_cost', 15, 2)->nullable();
            $table->decimal('lss_inspection_cost', 15, 2)->nullable();
            $table->decimal('sst', 15, 2)->nullable();
            $table->decimal('others_cost', 15, 2)->nullable();
            $table->decimal('total_cost', 15, 2)->nullable();
            $table->string('implementation_period')->nullable();
            $table->unsignedBigInteger('division_id')->nullable();
            $table->unsignedBigInteger('district_id')->nullable();
            $table->unsignedBigInteger('parliament_location_id')->nullable();
            $table->unsignedBigInteger('dun_id')->nullable();
            $table->enum('site_layout', ['Yes', 'No'])->nullable();
            $table->unsignedBigInteger('land_title_status_id')->nullable();
            $table->enum('consultation_service', ['Yes', 'No'])->nullable();
            $table->unsignedBigInteger('implementing_agency_id')->nullable();
            $table->unsignedBigInteger('implementation_method_id')->nullable();
            $table->unsignedBigInteger('project_ownership_id')->nullable();
            $table->string('jkkk_name')->nullable();
            $table->enum('state_government_asset', ['Yes', 'No'])->nullable();
            $table->enum('bill_of_quantity', ['Yes', 'No'])->nullable();
            $table->string('bill_of_quantity_attachment')->nullable();
            $table->enum('status', ['Active', 'NOC', 'Cancelled'])->default('Active');
            
            $table->timestamps();
            
            // Foreign keys
            $table->foreign('pre_project_id')->references('id')->on('pre_projects')->onDelete('restrict');
            $table->foreign('residen_category_id')->references('id')->on('residen_categories')->onDelete('set null');
            $table->foreign('agency_category_id')->references('id')->on('agency_categories')->onDelete('set null');
            $table->foreign('parliament_id')->references('id')->on('parliaments')->onDelete('set null');
            $table->foreign('dun_basic_id')->references('id')->on('duns')->onDelete('set null');
            $table->foreign('project_category_id')->references('id')->on('project_categories')->onDelete('set null');
            $table->foreign('division_id')->references('id')->on('divisions')->onDelete('set null');
            $table->foreign('district_id')->references('id')->on('districts')->onDelete('set null');
            $table->foreign('parliament_location_id')->references('id')->on('parliaments')->onDelete('set null');
            $table->foreign('dun_id')->references('id')->on('duns')->onDelete('set null');
            $table->foreign('land_title_status_id')->references('id')->on('land_title_statuses')->onDelete('set null');
            $table->foreign('implementing_agency_id')->references('id')->on('agency_categories')->onDelete('set null');
            $table->foreign('implementation_method_id')->references('id')->on('implementation_methods')->onDelete('set null');
            $table->foreign('project_ownership_id')->references('id')->on('project_ownerships')->onDelete('set null');
            
            // Indexes
            $table->index('project_number');
            $table->index('pre_project_id');
            $table->index('parliament_id');
            $table->index('dun_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
