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
        Schema::create('parliament_budgets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parliament_id')->constrained()->onDelete('cascade');
            $table->year('year');
            $table->decimal('budget', 15, 2);
            $table->timestamps();
            
            // Unique constraint on (parliament_id, year)
            $table->unique(['parliament_id', 'year'], 'unique_parliament_year');
            
            // Index for query performance
            $table->index(['parliament_id', 'year'], 'idx_parliament_year');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('parliament_budgets');
    }
};
