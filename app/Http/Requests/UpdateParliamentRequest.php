<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateParliamentRequest extends FormRequest
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
        $parliamentId = $this->route('id');
        
        return [
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:parliaments,code,' . $parliamentId,
            'description' => 'nullable|string',
            'status' => 'required|in:Active,Inactive',
            'budgets' => 'required|array|min:1',
            'budgets.*.year' => 'required|integer|min:2024|max:2030',
            'budgets.*.budget' => 'required|numeric|min:0|max:9999999999999.99',
        ];
    }

    /**
     * Configure the validator instance.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return void
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $budgets = $this->input('budgets', []);
            
            if (empty($budgets)) {
                return;
            }
            
            // Extract years from budget entries
            $years = array_column($budgets, 'year');
            
            // Check for duplicate years
            if (count($years) !== count(array_unique($years))) {
                // Find which years are duplicated
                $yearCounts = array_count_values($years);
                $duplicates = array_filter($yearCounts, function($count) {
                    return $count > 1;
                });
                
                $duplicateYears = implode(', ', array_keys($duplicates));
                
                $validator->errors()->add(
                    'budgets',
                    "The year {$duplicateYears} already exists for this Parliament. Each year can only be added once."
                );
            }
        });
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Parliament name is required.',
            'name.string' => 'Parliament name must be a text value.',
            'name.max' => 'Parliament name must not exceed 255 characters.',
            
            'code.required' => 'Parliament code is required.',
            'code.string' => 'Parliament code must be a text value.',
            'code.max' => 'Parliament code must not exceed 50 characters.',
            'code.unique' => 'This Parliament code is already in use.',
            
            'status.required' => 'Status is required.',
            'status.in' => 'Status must be either Active or Inactive.',
            
            'budgets.required' => 'At least one year-budget entry is required.',
            'budgets.array' => 'Budget entries must be in a valid format.',
            'budgets.min' => 'At least one year-budget entry is required.',
            
            'budgets.*.year.required' => 'Year is required for all budget entries.',
            'budgets.*.year.integer' => 'Year must be a valid number.',
            'budgets.*.year.min' => 'Year must be 2024 or later.',
            'budgets.*.year.max' => 'Year must be 2030 or earlier.',
            
            'budgets.*.budget.required' => 'Budget amount is required for all entries.',
            'budgets.*.budget.numeric' => 'Budget amount must be a valid number.',
            'budgets.*.budget.min' => 'Budget amount must be a positive number.',
            'budgets.*.budget.max' => 'Budget amount must not exceed 9,999,999,999,999.99.',
        ];
    }
}
