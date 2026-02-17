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
        Schema::create('nocs', function (Blueprint $table) {
            $table->id();
            $table->string('noc_number')->unique(); // e.g., NOC/2026/001
            $table->foreignId('parliament_id')->nullable()->constrained('parliaments')->onDelete('set null');
            $table->foreignId('dun_id')->nullable()->constrained('duns')->onDelete('set null');
            $table->date('noc_date');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade'); // User who created NOC
            $table->enum('status', ['Draft', 'Pending First Approval', 'Pending Second Approval', 'Approved', 'Rejected'])->default('Draft');
            $table->foreignId('first_approver_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('first_approved_at')->nullable();
            $table->text('first_approval_remarks')->nullable();
            $table->foreignId('second_approver_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('second_approved_at')->nullable();
            $table->text('second_approval_remarks')->nullable();
            $table->timestamps();
        });

        // Pivot table for NOC and PreProjects (many-to-many)
        Schema::create('noc_pre_project', function (Blueprint $table) {
            $table->id();
            $table->foreignId('noc_id')->constrained('nocs')->onDelete('cascade');
            $table->foreignId('pre_project_id')->constrained('pre_projects')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('noc_pre_project');
        Schema::dropIfExists('nocs');
    }
};
