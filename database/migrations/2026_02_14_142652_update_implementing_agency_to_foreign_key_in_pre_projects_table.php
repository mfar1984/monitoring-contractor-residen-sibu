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
            // Drop old column
            $table->dropColumn('implementing_agency');
            
            // Add new foreign key column
            $table->foreignId('implementing_agency_id')->nullable()->after('consultation_service')->constrained('agency_categories')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pre_projects', function (Blueprint $table) {
            // Drop foreign key
            $table->dropForeign(['implementing_agency_id']);
            $table->dropColumn('implementing_agency_id');
            
            // Restore old column
            $table->string('implementing_agency')->nullable()->after('consultation_service');
        });
    }
};
