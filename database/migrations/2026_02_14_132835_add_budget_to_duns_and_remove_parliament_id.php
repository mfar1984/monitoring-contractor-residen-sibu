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
        Schema::table('duns', function (Blueprint $table) {
            $table->dropForeign(['parliament_id']);
            $table->dropColumn('parliament_id');
            $table->decimal('budget', 15, 2)->after('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('duns', function (Blueprint $table) {
            $table->foreignId('parliament_id')->after('id')->constrained('parliaments')->onDelete('cascade');
            $table->dropColumn('budget');
        });
    }
};
