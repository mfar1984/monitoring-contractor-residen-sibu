<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Services\BudgetCalculationService;
use Illuminate\Support\Facades\Auth;

class StorePreProjectRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'project_year' => 'required|integer|min:2024|max:2030',
            'total_cost' => [
                'required',
                'numeric',
                'min:0',
                function ($attribute, $value, $fail) {
                    $budgetService = new BudgetCalculationService();
                    $user = Auth::user();
                    
                    // Get the project year from the request
                    $year = $this->input('project_year');
                    
                    if (!$year) {
                        $fail('Project year is required for budget validation.');
                        return;
                    }
                    
                    // Skip budget validation for Residen users
                    if (!$budgetService->isSubjectToBudgetValidation($user)) {
                        return;
                    }
                    
                    // Check if budget exists for the selected year
                    $budgetInfo = $budgetService->getUserBudgetInfo($user, $year);
                    if ($budgetInfo['total_budget'] <= 0) {
                        $fail("No budget has been allocated for year {$year}. Please contact your administrator.");
                        return;
                    }
                    
                    // Check if project cost would exceed budget
                    if ($budgetService->wouldExceedBudget($user, $value, null, $year)) {
                        $fail("The project cost of RM " . number_format($value, 2) . " exceeds the remaining budget of RM " . number_format($budgetInfo['remaining_budget'], 2) . " for year {$year}.");
                    }
                },
            ],
            'residen_category_id' => 'nullable|exists:residen_categories,id',
            'agency_category_id' => 'nullable|exists:agency_categories,id',
            'project_category_id' => 'nullable|exists:project_categories,id',
            'project_scope' => 'nullable|string',
            'actual_project_cost' => 'nullable|numeric|min:0',
            'consultation_cost' => 'nullable|numeric|min:0',
            'lss_inspection_cost' => 'nullable|numeric|min:0',
            'sst' => 'nullable|numeric|min:0',
            'others_cost' => 'nullable|numeric|min:0',
            'implementation_period' => 'nullable|string|max:255',
            'division_id' => 'nullable|exists:divisions,id',
            'district_id' => 'nullable|exists:districts,id',
            'parliament_location_id' => 'nullable|exists:parliaments,id',
            'dun_id' => 'nullable|exists:duns,id',
            'site_layout' => 'nullable|in:Yes,No',
            'consultation_service' => 'nullable|in:Yes,No',
            'land_title_status_id' => 'nullable|exists:land_title_statuses,id',
            'implementing_agency_id' => 'nullable|exists:agency_categories,id',
            'implementation_method_id' => 'nullable|exists:implementation_methods,id',
            'project_ownership_id' => 'nullable|exists:project_ownerships,id',
            'jkkk_name' => 'nullable|string|max:255',
            'state_government_asset' => 'nullable|in:Yes,No',
            'bill_of_quantity' => 'nullable|in:Yes,No',
            'bill_of_quantity_attachment' => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx|max:10240',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Project name is required.',
            'project_year.required' => 'Project year is required.',
            'project_year.integer' => 'Project year must be a valid year.',
            'project_year.min' => 'Project year must be between 2024 and 2030.',
            'project_year.max' => 'Project year must be between 2024 and 2030.',
            'total_cost.required' => 'Total cost is required.',
            'total_cost.numeric' => 'Total cost must be a number.',
            'total_cost.min' => 'Total cost must be at least 0.',
            'bill_of_quantity_attachment.mimes' => 'Attachment must be a PDF, DOC, DOCX, XLS, or XLSX file.',
            'bill_of_quantity_attachment.max' => 'Attachment size must not exceed 10MB.',
        ];
    }
}
