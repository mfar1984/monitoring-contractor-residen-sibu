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
            $table->timestamp('submitted_to_epu_at')->nullable()->after('status');
            $table->unsignedBigInteger('submitted_to_epu_by')->nullable()->after('submitted_to_epu_at');
            
            $table->foreign('submitted_to_epu_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pre_projects', function (Blueprint $table) {
            $table->dropForeign(['submitted_to_epu_by']);
            $table->dropColumn(['submitted_to_epu_at', 'submitted_to_epu_by']);
        });
    }
};
