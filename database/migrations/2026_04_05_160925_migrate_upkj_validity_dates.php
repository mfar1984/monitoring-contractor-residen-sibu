<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $records = DB::table('contractor_upkj_records')->get();
        $failed = [];
        $migrated = 0;
        
        foreach ($records as $record) {
            try {
                $updates = [];
                
                // Parse validity_period (format: "DD/MM/YYYY - DD/MM/YYYY")
                if ($record->validity_period && strpos($record->validity_period, ' - ') !== false) {
                    $dates = explode(' - ', $record->validity_period);
                    if (count($dates) === 2) {
                        $fromStr = trim($dates[0]);
                        $toStr = trim($dates[1]);
                        
                        try {
                            $validityFrom = Carbon::createFromFormat('d/m/Y', $fromStr)->format('Y-m-d');
                            $validityTo = Carbon::createFromFormat('d/m/Y', $toStr)->format('Y-m-d');
                            
                            $updates['validity_from'] = $validityFrom;
                            $updates['validity_to'] = $validityTo;
                        } catch (\Exception $e) {
                            $failed[] = [
                                'id' => $record->id,
                                'contractor_id' => $record->contractor_category_id,
                                'field' => 'validity_period',
                                'value' => $record->validity_period,
                                'error' => 'Invalid date format: ' . $e->getMessage(),
                            ];
                        }
                    }
                }
                
                // Parse bumiputera_validity (format: "DD/MM/YYYY - DD/MM/YYYY")
                if ($record->bumiputera_validity && strpos($record->bumiputera_validity, ' - ') !== false) {
                    $dates = explode(' - ', $record->bumiputera_validity);
                    if (count($dates) === 2) {
                        $fromStr = trim($dates[0]);
                        $toStr = trim($dates[1]);
                        
                        try {
                            $bumiFrom = Carbon::createFromFormat('d/m/Y', $fromStr)->format('Y-m-d');
                            $bumiTo = Carbon::createFromFormat('d/m/Y', $toStr)->format('Y-m-d');
                            
                            $updates['bumiputera_from'] = $bumiFrom;
                            $updates['bumiputera_to'] = $bumiTo;
                        } catch (\Exception $e) {
                            $failed[] = [
                                'id' => $record->id,
                                'contractor_id' => $record->contractor_category_id,
                                'field' => 'bumiputera_validity',
                                'value' => $record->bumiputera_validity,
                                'error' => 'Invalid date format: ' . $e->getMessage(),
                            ];
                        }
                    }
                }
                
                // Update record if we have any date updates
                if (!empty($updates)) {
                    DB::table('contractor_upkj_records')
                        ->where('id', $record->id)
                        ->update($updates);
                    $migrated++;
                }
                
            } catch (\Exception $e) {
                $failed[] = [
                    'id' => $record->id,
                    'contractor_id' => $record->contractor_category_id,
                    'error' => 'General error: ' . $e->getMessage(),
                ];
            }
        }
        
        // Log results
        Log::info("UPKJ Date Migration Complete", [
            'total_records' => $records->count(),
            'migrated' => $migrated,
            'failed' => count($failed),
        ]);
        
        if (!empty($failed)) {
            Log::warning('Failed to migrate some UPKJ date records', $failed);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Set all date columns to NULL
        DB::table('contractor_upkj_records')->update([
            'validity_from' => null,
            'validity_to' => null,
            'bumiputera_from' => null,
            'bumiputera_to' => null,
        ]);
    }
};
