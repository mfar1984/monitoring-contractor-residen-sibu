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
            $table->text('first_approval_remarks')->nullable()->after('first_approved_at');
            $table->text('second_approval_remarks')->nullable()->after('second_approved_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pre_projects', function (Blueprint $table) {
            $table->dropColumn(['first_approval_remarks', 'second_approval_remarks']);
        });
    }
};
