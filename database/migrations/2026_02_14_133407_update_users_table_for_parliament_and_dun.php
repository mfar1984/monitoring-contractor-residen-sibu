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
            // Add new columns for parliament and dun
            $table->foreignId('parliament_id')->nullable()->after('parliament_category_id')->constrained('parliaments')->onDelete('set null');
            $table->foreignId('dun_id')->nullable()->after('parliament_id')->constrained('duns')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['parliament_id']);
            $table->dropForeign(['dun_id']);
            $table->dropColumn(['parliament_id', 'dun_id']);
        });
    }
};
