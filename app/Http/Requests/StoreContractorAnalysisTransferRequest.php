<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Project;

class StoreContractorAnalysisTransferRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = auth()->user();
        return $user->agency_category_id || $user->residen_category_id;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'project_ids' => 'required|array|min:1',
            'project_ids.*' => 'required|exists:projects,id',
            'attachment' => 'required|file|mimes:pdf,doc,docx|max:5120', // 5MB
        ];
    }

    /**
     * Get custom error messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'project_ids.required' => 'At least one project must be selected',
            'project_ids.min' => 'At least one project must be selected',
            'project_ids.*.exists' => 'One or more selected projects are invalid',
            'attachment.required' => 'Application letter is required',
            'attachment.mimes' => 'Attachment must be a PDF, DOC, or DOCX file',
            'attachment.max' => 'Attachment size must not exceed 5MB',
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
            $user = auth()->user();
            
            // For Agency users, verify all selected projects belong to their agency
            if ($user->agency_category_id) {
                $projectIds = $this->input('project_ids', []);
                $invalidProjects = Project::whereIn('id', $projectIds)
                    ->where('agency_category_id', '!=', $user->agency_category_id)
                    ->count();
                
                if ($invalidProjects > 0) {
                    $validator->errors()->add(
                        'project_ids',
                        'You can only select projects from your agency'
                    );
                }
            }
        });
    }
}
