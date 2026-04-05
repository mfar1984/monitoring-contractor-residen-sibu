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
        Schema::table('contractor_analysis_transfers', function (Blueprint $table) {
            $table->json('upkj_classes')->nullable()->after('attachment_path');
            $table->json('upkj_heads')->nullable()->after('upkj_classes');
            $table->json('upkj_subheads')->nullable()->after('upkj_heads');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contractor_analysis_transfers', function (Blueprint $table) {
            $table->dropColumn(['upkj_classes', 'upkj_heads', 'upkj_subheads']);
        });
    }
};
