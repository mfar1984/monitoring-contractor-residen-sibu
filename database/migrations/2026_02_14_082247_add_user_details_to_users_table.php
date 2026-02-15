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
            $table->string('full_name')->after('username');
            $table->foreignId('residen_category_id')->nullable()->after('full_name')->constrained('residen_categories')->onDelete('set null');
            $table->string('department')->nullable()->after('residen_category_id');
            $table->string('contact_number')->nullable()->after('department');
            $table->string('email')->nullable()->after('contact_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['residen_category_id']);
            $table->dropColumn(['full_name', 'residen_category_id', 'department', 'contact_number', 'email']);
        });
    }
};
