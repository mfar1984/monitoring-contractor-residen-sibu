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
            'total_cost' => [
                'required',
                'numeric',
                'min:0',
                function ($attribute, $value, $fail) {
                    $budgetService = new BudgetCalculationService();
                    $user = Auth::user();
                    
                    if (!$budgetService->isWithinBudget($user, $value)) {
                        $budgetInfo = $budgetService->getUserBudgetInfo($user);
                        $fail("Budget exceeded. Remaining budget: RM " . number_format($budgetInfo['remaining_budget'], 2));
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
            'total_cost.required' => 'Total cost is required.',
            'total_cost.numeric' => 'Total cost must be a number.',
            'total_cost.min' => 'Total cost must be at least 0.',
            'bill_of_quantity_attachment.mimes' => 'Attachment must be a PDF, DOC, DOCX, XLS, or XLSX file.',
            'bill_of_quantity_attachment.max' => 'Attachment size must not exceed 10MB.',
        ];
    }
}
