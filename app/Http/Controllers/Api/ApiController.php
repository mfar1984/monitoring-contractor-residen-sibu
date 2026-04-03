<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UpkjClassification;
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
}
