<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UpkjClassification;
use App\Models\ContractorCategory;
use App\Models\ContractorUpkjRecord;
use Illuminate\Http\Request;

class ApiController extends Controller
{
    /**
     * Get UPKJ classifications filtered by category
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUpkjClassifications(Request $request)
    {
        $query = UpkjClassification::where('status', 'active');

        // Filter by category if provided
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('class', 'like', "%{$search}%")
                  ->orWhere('head_code', 'like', "%{$search}%")
                  ->orWhere('subhead_code', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $classifications = $query
            ->select('id', 'class', 'head_code', 'subhead_code', 'subhead_letter', 'subhead_roman', 'description')
            ->orderBy('class')
            ->orderBy('head_code')
            ->orderBy('subhead_code')
            ->get();

        return response()->json($classifications);
    }

    /**
     * Get districts filtered by division
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDistricts(Request $request)
    {
        $query = \App\Models\District::where('status', 'Active');

        // Filter by division if provided
        if ($request->filled('division_id')) {
            $query->where('division_id', $request->division_id);
        }

        $districts = $query
            ->select('id', 'name', 'code', 'division_id')
            ->orderBy('name')
            ->get();

        return response()->json($districts);
    }

    /**
     * Get available UPKJ heads based on selected categories and classes
     * Returns only heads that have contractors
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAvailableUpkjHeads(Request $request)
    {
        $categories = $request->input('categories', []);
        $classes = $request->input('classes', []);
        
        // Ensure arrays
        $categories = is_array($categories) ? $categories : [$categories];
        $classes = is_array($classes) ? $classes : [$classes];
        
        // Remove empty values
        $categories = array_filter($categories);
        $classes = array_filter($classes);
        
        if (empty($categories) || empty($classes)) {
            return response()->json([]);
        }
        
        // Get all contractors with UPKJ records matching categories and classes
        $contractors = ContractorCategory::whereHas('upkjRecords', function($query) use ($categories, $classes) {
            $query->where(function($q) use ($categories, $classes) {
                foreach ($classes as $class) {
                    $q->orWhereRaw("JSON_SEARCH(classifications, 'one', ?, NULL, '$[*].class') IS NOT NULL", [$class]);
                }
            });
        })
        ->with(['upkjRecords' => function($query) {
            $query->select('id', 'contractor_category_id', 'classifications');
        }])
        ->where('status', 'Active')
        ->get();
        
        // Extract unique heads from contractor classifications
        $headsMap = [];
        foreach ($contractors as $contractor) {
            foreach ($contractor->upkjRecords as $record) {
                foreach ($record->classifications as $classification) {
                    if (in_array($classification['class'], $classes) && !empty($classification['head_code'])) {
                        $headCode = $classification['head_code'];
                        if (!isset($headsMap[$headCode])) {
                            $headsMap[$headCode] = [
                                'head_code' => $headCode,
                                'head_name' => $classification['head_name'] ?? ''
                            ];
                        }
                    }
                }
            }
        }
        
        // Sort by head_code
        ksort($headsMap);
        
        return response()->json(array_values($headsMap));
    }
    
    /**
     * Get available UPKJ subheads based on selected categories, classes, and heads
     * Returns only subheads that have contractors
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAvailableUpkjSubheads(Request $request)
    {
        $categories = $request->input('categories', []);
        $classes = $request->input('classes', []);
        $heads = $request->input('heads', []);
        
        // Ensure arrays
        $categories = is_array($categories) ? $categories : [$categories];
        $classes = is_array($classes) ? $classes : [$classes];
        $heads = is_array($heads) ? $heads : [$heads];
        
        // Remove empty values
        $categories = array_filter($categories);
        $classes = array_filter($classes);
        $heads = array_filter($heads);
        
        if (empty($categories) || empty($classes) || empty($heads)) {
            return response()->json([]);
        }
        
        // Get all contractors with UPKJ records matching categories, classes, and heads
        $contractors = ContractorCategory::whereHas('upkjRecords', function($query) use ($categories, $classes, $heads) {
            $query->where(function($q) use ($classes, $heads) {
                foreach ($heads as $head) {
                    $q->orWhereRaw("JSON_SEARCH(classifications, 'one', ?, NULL, '$[*].head_code') IS NOT NULL", [$head]);
                }
            });
        })
        ->with(['upkjRecords' => function($query) {
            $query->select('id', 'contractor_category_id', 'classifications');
        }])
        ->where('status', 'Active')
        ->get();
        
        // Extract unique subheads from contractor classifications
        $subheadsMap = [];
        foreach ($contractors as $contractor) {
            foreach ($contractor->upkjRecords as $record) {
                foreach ($record->classifications as $classification) {
                    if (in_array($classification['class'], $classes) && 
                        in_array($classification['head_code'], $heads) && 
                        !empty($classification['subhead_code'])) {
                        
                        $subheadValue = $classification['subhead_code'] . 
                                      ($classification['subhead_letter'] ?? '') . 
                                      ($classification['subhead_roman'] ?? '');
                        
                        if (!isset($subheadsMap[$subheadValue])) {
                            $subheadsMap[$subheadValue] = [
                                'subhead_value' => $subheadValue,
                                'description' => $classification['description'] ?? ''
                            ];
                        }
                    }
                }
            }
        }
        
        // Sort by subhead_value
        ksort($subheadsMap);
        
        return response()->json(array_values($subheadsMap));
    }

    /**
     * Filter contractors by UPKJ classifications (Multi-Select Cascade Filter)
     * 
     * Accepts: categories[], classes[], heads[], subheads[] (arrays for multiple selections)
     * Filter logic: Match contractors with ANY selected UPKJ combination (OR logic)
     * Returns only the selected UPKJ classifications in the response
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function filterContractorsByUpkj(Request $request)
    {
        $categories = $request->input('categories', []);
        $classes = $request->input('classes', []);
        $heads = $request->input('heads', []);
        $subheads = $request->input('subheads', []);
        
        // Ensure arrays
        $categories = is_array($categories) ? $categories : [$categories];
        $classes = is_array($classes) ? $classes : [$classes];
        $heads = is_array($heads) ? $heads : [$heads];
        $subheads = is_array($subheads) ? $subheads : [$subheads];
        
        // Remove empty values
        $categories = array_filter($categories);
        $classes = array_filter($classes);
        $heads = array_filter($heads);
        $subheads = array_filter($subheads);
        
        // Validate that at least one filter is provided
        if (empty($categories) && empty($classes) && empty($heads) && empty($subheads)) {
            return response()->json([
                'error' => 'At least one UPKJ filter must be provided'
            ], 400);
        }
        
        // Get contractors with UPKJ records matching the selected filters
        // Logic: Match contractors where classifications match ALL selected filter levels (AND)
        // Within each level, match ANY of the selected values (OR)
        $contractors = ContractorCategory::where('status', 'Active')
            ->whereHas('upkjRecords', function($query) use ($categories, $classes, $heads, $subheads) {
                // We need to check if contractor has at least one classification matching all criteria
                $query->where(function($q) use ($categories, $classes, $heads, $subheads) {
                    // For each contractor_upkj_record, check if it has matching classifications
                    // This is done by filtering the JSON array in PHP after retrieval
                    // For now, we just ensure the record exists
                    $q->whereNotNull('classifications');
                });
            })
            ->with(['upkjRecords' => function($query) {
                $query->select('id', 'contractor_category_id', 'category', 'registration_status', 'validity_period', 'certificate_no', 'classifications');
            }])
            ->select('id', 'company_name', 'code', 'registration_number', 'status', 'authorized_person_name', 'registered_address', 'telephone_no', 'email', 'upk_expiry_date')
            ->get();
        
        // Filter contractors in PHP to check if they have matching classifications
        $contractors = $contractors->filter(function($contractor) use ($categories, $classes, $heads, $subheads) {
            foreach ($contractor->upkjRecords as $record) {
                // Check if record category matches (category is at record level, not classification level)
                $recordMatchesCategory = empty($categories) || in_array($record->category, $categories);
                
                if (!$recordMatchesCategory) {
                    continue; // Skip this record if category doesn't match
                }
                
                foreach ($record->classifications as $classification) {
                    // Check if this classification matches the selected filter levels
                    // Note: category is checked at record level above
                    $matchesClass = empty($classes) || in_array($classification['class'] ?? '', $classes);
                    $matchesHead = empty($heads) || in_array($classification['head_code'] ?? '', $heads);
                    
                    // For subhead matching, construct the full subhead value
                    if (!empty($subheads)) {
                        $subheadValue = ($classification['subhead_code'] ?? '') . 
                                      ($classification['subhead_letter'] ?? '') . 
                                      ($classification['subhead_roman'] ?? '');
                        $matchesSubhead = in_array($subheadValue, $subheads);
                    } else {
                        $matchesSubhead = true; // If no subhead filter, match all
                    }
                    
                    // If this classification matches all criteria, include this contractor
                    if ($matchesClass && $matchesHead && $matchesSubhead) {
                        return true;
                    }
                }
            }
            return false;
        })->values();
        
        $contractors = $contractors->sortBy('company_name')->values();
        
        // Format response with ONLY selected UPKJ classifications
        $formattedContractors = $contractors->map(function($contractor) use ($categories, $classes, $heads, $subheads) {
            return [
                'id' => $contractor->id,
                'company_name' => $contractor->company_name,
                'code' => $contractor->code,
                'registration_number' => $contractor->registration_number,
                'status' => $contractor->status, // Active or Inactive
                'authorized_person_name' => $contractor->authorized_person_name ?? '-',
                'registered_address' => $contractor->registered_address ?? '-',
                'telephone_no' => $contractor->telephone_no ?? '-',
                'email' => $contractor->email ?? '-',
                'upk_expiry_date' => $contractor->upk_expiry_date ? $contractor->upk_expiry_date->format('d/m/Y') : null,
                'is_expired' => $contractor->upk_expiry_date ? $contractor->upk_expiry_date->isPast() : false,
                'upkj_records' => $contractor->upkjRecords->map(function($record) use ($categories, $classes, $heads, $subheads) {
                    // Check if record category matches
                    $recordMatchesCategory = empty($categories) || in_array($record->category, $categories);
                    
                    if (!$recordMatchesCategory) {
                        return null; // Skip this record
                    }
                    
                    // Filter classifications to only show selected ones
                    $filteredClassifications = collect($record->classifications)->filter(function($c) use ($classes, $heads, $subheads) {
                        // Check if this classification matches the selected filters
                        // Note: category is checked at record level above
                        $matchesClass = empty($classes) || in_array($c['class'] ?? '', $classes);
                        $matchesHead = empty($heads) || in_array($c['head_code'] ?? '', $heads);
                        
                        // For subhead matching, need to construct the full subhead value
                        $subheadValue = ($c['subhead_code'] ?? '') . 
                                      ($c['subhead_letter'] ?? '') . 
                                      ($c['subhead_roman'] ?? '');
                        $matchesSubhead = empty($subheads) || in_array($subheadValue, $subheads);
                        
                        return $matchesClass && $matchesHead && $matchesSubhead;
                    })->map(function($c) {
                        $parts = [];
                        if (!empty($c['class'])) $parts[] = $c['class'];
                        if (!empty($c['head_code'])) $parts[] = $c['head_code'];
                        if (!empty($c['subhead_code'])) {
                            $subhead = $c['subhead_code'] . 
                                     ($c['subhead_letter'] ?? '') . 
                                     ($c['subhead_roman'] ?? '');
                            $parts[] = $subhead;
                        }
                        return implode('-', $parts);
                    });
                    
                    return [
                        'category' => $record->category,
                        'certificate_no' => $record->certificate_no ?? '-',
                        'status' => $record->registration_status,
                        'validity_period' => $record->validity_period,
                        'is_expired' => $this->isUpkjExpired($record->validity_period),
                        'classifications' => $filteredClassifications->join(', ')
                    ];
                })->filter(function($record) {
                    // Remove null records and records with no matching classifications
                    return $record !== null && !empty($record['classifications']);
                })->values()
            ];
        })->filter(function($contractor) {
            // Remove contractors with no matching UPKJ records
            return $contractor['upkj_records']->isNotEmpty();
        })->values();
        
        return response()->json($formattedContractors);
    }
    
    /**
     * Check if UPKJ validity period is expired
     * 
     * @param string|null $validityPeriod Format: "DD/MM/YYYY - DD/MM/YYYY" or "DD/MM/YYYY"
     * @return bool
     */
    private function isUpkjExpired($validityPeriod): bool
    {
        if (empty($validityPeriod)) {
            return false;
        }
        
        try {
            // Check if it's a date range (contains " - ")
            if (strpos($validityPeriod, ' - ') !== false) {
                // Split the date range and get the end date
                $dates = explode(' - ', $validityPeriod);
                $endDate = trim($dates[1]);
                
                // Parse DD/MM/YYYY format
                $date = \Carbon\Carbon::createFromFormat('d/m/Y', $endDate);
                return $date->isPast();
            } else {
                // Single date format
                $date = \Carbon\Carbon::createFromFormat('d/m/Y', trim($validityPeriod));
                return $date->isPast();
            }
        } catch (\Exception $e) {
            // If parsing fails, assume not expired
            return false;
        }
    }
}
