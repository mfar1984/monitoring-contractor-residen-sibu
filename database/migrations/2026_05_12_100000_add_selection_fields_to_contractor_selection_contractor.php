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
        Schema::table('contractor_selection_contractor', function (Blueprint $table) {
            // Add selected field (YES/NO)
            $table->enum('selected', ['YES', 'NO'])->nullable()->after('snapshot_data');
            
            // Add reason field
            $table->text('reason')->nullable()->after('selected');
            
            // Add index for selected field
            $table->index('selected');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contractor_selection_contractor', function (Blueprint $table) {
            $table->dropIndex(['selected']);
            $table->dropColumn(['selected', 'reason']);
        });
    }
};
