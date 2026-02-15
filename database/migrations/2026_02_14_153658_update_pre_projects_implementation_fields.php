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
        Schema::table('pre_projects', function (Blueprint $table) {
            // Drop foreign key constraint first
            $table->dropForeign(['project_ownership_parliament_id']);
            
            // Drop old columns
            $table->dropColumn(['implementation_method', 'project_ownership_parliament_id']);
            
            // Add new foreign key columns
            $table->foreignId('implementation_method_id')->nullable()->after('implementing_agency_id')->constrained('implementation_methods')->onDelete('set null');
            $table->foreignId('project_ownership_id')->nullable()->after('implementation_method_id')->constrained('project_ownerships')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pre_projects', function (Blueprint $table) {
            // Drop foreign key columns
            $table->dropForeign(['implementation_method_id']);
            $table->dropForeign(['project_ownership_id']);
            $table->dropColumn(['implementation_method_id', 'project_ownership_id']);
            
            // Restore old columns
            $table->string('implementation_method')->nullable();
            $table->foreignId('project_ownership_parliament_id')->nullable()->constrained('parliaments')->onDelete('set null');
        });
    }
};
