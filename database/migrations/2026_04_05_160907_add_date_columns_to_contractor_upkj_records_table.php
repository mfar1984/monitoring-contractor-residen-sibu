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
        Schema::table('contractor_upkj_records', function (Blueprint $table) {
            $table->date('validity_from')->nullable()->after('validity_period');
            $table->date('validity_to')->nullable()->after('validity_from');
            $table->date('bumiputera_from')->nullable()->after('bumiputera_validity');
            $table->date('bumiputera_to')->nullable()->after('bumiputera_from');
            
            // Add indexes for faster expiry queries
            $table->index('validity_to');
            $table->index('bumiputera_to');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contractor_upkj_records', function (Blueprint $table) {
            $table->dropIndex(['validity_to']);
            $table->dropIndex(['bumiputera_to']);
            $table->dropColumn(['validity_from', 'validity_to', 'bumiputera_from', 'bumiputera_to']);
        });
    }
};
