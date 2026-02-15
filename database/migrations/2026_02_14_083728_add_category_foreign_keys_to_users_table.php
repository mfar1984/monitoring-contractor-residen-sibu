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
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('agency_category_id')->nullable()->after('residen_category_id')->constrained('agency_categories')->onDelete('set null');
            $table->foreignId('parliament_category_id')->nullable()->after('agency_category_id')->constrained('parliament_categories')->onDelete('set null');
            $table->foreignId('contractor_category_id')->nullable()->after('parliament_category_id')->constrained('contractor_categories')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['agency_category_id']);
            $table->dropForeign(['parliament_category_id']);
            $table->dropForeign(['contractor_category_id']);
            $table->dropColumn(['agency_category_id', 'parliament_category_id', 'contractor_category_id']);
        });
    }
};
