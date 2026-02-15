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
            $table->foreignId('dun_basic_id')->nullable()->after('parliament_id')->constrained('duns')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pre_projects', function (Blueprint $table) {
            $table->dropForeign(['dun_basic_id']);
            $table->dropColumn('dun_basic_id');
        });
    }
};
