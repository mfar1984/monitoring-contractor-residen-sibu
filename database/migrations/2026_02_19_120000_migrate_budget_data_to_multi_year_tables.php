<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * This migration transfers existing budget data from single-budget columns
     * to the new multi-year budget tables.
     */
    public function up(): void
    {
        $currentYear = now()->year;
        
        // Check if budget column exists in parliaments table before migrating
        if (Schema::hasColumn('parliaments', 'budget')) {
            try {
                // Migrate parliament budget data
                $parliaments = DB::table('parliaments')
                    ->whereNotNull('budget')
                    ->where('budget', '>', 0)
                    ->get();
                
                foreach ($parliaments as $parliament) {
                    try {
                        DB::table('parliament_budgets')->insert([
                            'parliament_id' => $parliament->id,
                            'year' => $currentYear,
                            'budget' => $parliament->budget,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    } catch (\Exception $e) {
                        // Log error but continue with other records
                        Log::warning("Failed to migrate budget for parliament ID {$parliament->id}: {$e->getMessage()}");
                    }
                }
                
                // Drop budget column from parliaments table
                Schema::table('parliaments', function (Blueprint $table) {
                    $table->dropColumn('budget');
                });
                
            } catch (\Exception $e) {
                Log::error("Error during parliament budget migration: {$e->getMessage()}");
                throw $e;
            }
        }
        
        // Check if budget column exists in duns table before migrating
        if (Schema::hasColumn('duns', 'budget')) {
            try {
                // Migrate dun budget data
                $duns = DB::table('duns')
                    ->whereNotNull('budget')
                    ->where('budget', '>', 0)
                    ->get();
                
                foreach ($duns as $dun) {
                    try {
                        DB::table('dun_budgets')->insert([
                            'dun_id' => $dun->id,
                            'year' => $currentYear,
                            'budget' => $dun->budget,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    } catch (\Exception $e) {
                        // Log error but continue with other records
                        Log::warning("Failed to migrate budget for dun ID {$dun->id}: {$e->getMessage()}");
                    }
                }
                
                // Drop budget column from duns table
                Schema::table('duns', function (Blueprint $table) {
                    $table->dropColumn('budget');
                });
                
            } catch (\Exception $e) {
                Log::error("Error during dun budget migration: {$e->getMessage()}");
                throw $e;
            }
        }
    }

    /**
     * Reverse the migrations.
     * 
     * This rollback restores the original single-budget column structure
     * and copies current year budget data back.
     */
    public function down(): void
    {
        $currentYear = now()->year;
        
        // Restore budget column to parliaments table
        if (!Schema::hasColumn('parliaments', 'budget')) {
            Schema::table('parliaments', function (Blueprint $table) {
                $table->decimal('budget', 15, 2)->after('name')->default(0);
            });
        }
        
        // Restore parliament budget data from current year
        try {
            $parliamentBudgets = DB::table('parliament_budgets')
                ->where('year', $currentYear)
                ->get();
            
            foreach ($parliamentBudgets as $budget) {
                try {
                    DB::table('parliaments')
                        ->where('id', $budget->parliament_id)
                        ->update(['budget' => $budget->budget]);
                } catch (\Exception $e) {
                    Log::warning("Failed to restore budget for parliament ID {$budget->parliament_id}: {$e->getMessage()}");
                }
            }
        } catch (\Exception $e) {
            Log::error("Error during parliament budget restoration: {$e->getMessage()}");
        }
        
        // Restore budget column to duns table
        if (!Schema::hasColumn('duns', 'budget')) {
            Schema::table('duns', function (Blueprint $table) {
                $table->decimal('budget', 15, 2)->after('name')->default(0);
            });
        }
        
        // Restore dun budget data from current year
        try {
            $dunBudgets = DB::table('dun_budgets')
                ->where('year', $currentYear)
                ->get();
            
            foreach ($dunBudgets as $budget) {
                try {
                    DB::table('duns')
                        ->where('id', $budget->dun_id)
                        ->update(['budget' => $budget->budget]);
                } catch (\Exception $e) {
                    Log::warning("Failed to restore budget for dun ID {$budget->dun_id}: {$e->getMessage()}");
                }
            }
        } catch (\Exception $e) {
            Log::error("Error during dun budget restoration: {$e->getMessage()}");
        }
    }
};
