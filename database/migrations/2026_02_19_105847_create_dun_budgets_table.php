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
        Schema::create('dun_budgets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dun_id')->constrained()->onDelete('cascade');
            $table->year('year');
            $table->decimal('budget', 15, 2);
            $table->timestamps();
            
            // Unique constraint on (dun_id, year)
            $table->unique(['dun_id', 'year'], 'unique_dun_year');
            
            // Index for query performance
            $table->index(['dun_id', 'year'], 'idx_dun_year');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dun_budgets');
    }
};
