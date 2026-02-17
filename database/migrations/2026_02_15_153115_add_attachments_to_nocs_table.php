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
        Schema::table('nocs', function (Blueprint $table) {
            $table->string('noc_letter_attachment')->nullable()->after('second_approval_remarks');
            $table->string('noc_project_list_attachment')->nullable()->after('noc_letter_attachment');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('nocs', function (Blueprint $table) {
            $table->dropColumn(['noc_letter_attachment', 'noc_project_list_attachment']);
        });
    }
};
