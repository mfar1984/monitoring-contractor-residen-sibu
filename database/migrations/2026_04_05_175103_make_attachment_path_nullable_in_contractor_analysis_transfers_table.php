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
            $table->string('attachment_path', 255)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contractor_analysis_transfers', function (Blueprint $table) {
            $table->string('attachment_path', 255)->nullable(false)->change();
        });
    }
};
