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
            $table->unsignedBigInteger('first_approver_id')->nullable()->after('submitted_to_epu_by');
            $table->timestamp('first_approved_at')->nullable()->after('first_approver_id');
            $table->unsignedBigInteger('second_approver_id')->nullable()->after('first_approved_at');
            $table->timestamp('second_approved_at')->nullable()->after('second_approver_id');
            
            $table->foreign('first_approver_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('second_approver_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pre_projects', function (Blueprint $table) {
            $table->dropForeign(['first_approver_id']);
            $table->dropForeign(['second_approver_id']);
            $table->dropColumn(['first_approver_id', 'first_approved_at', 'second_approver_id', 'second_approved_at']);
        });
    }
};
