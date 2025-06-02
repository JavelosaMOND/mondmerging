<?php

namespace App\Http\Requests;

use App\Models\ReportType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ReportSubmissionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::check() && Auth::user()->role === 'barangay';
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'report_type_id' => 'required|exists:report_types,id',
            'remarks' => 'nullable|string',
            'file' => 'nullable|file|max:10240', // 10MB max
        ];

        // Get the report type to add frequency-specific validation
        $reportTypeId = $this->input('report_type_id');
        
        if ($reportTypeId) {
            $reportType = ReportType::find($reportTypeId);
            
            if ($reportType) {
                switch ($reportType->frequency) {
                    case 'weekly':
                        $rules = array_merge($rules, [
                            'month' => 'required|string',
                            'week_number' => 'required|integer|min:1|max:5',
                            'num_of_clean_up_sites' => 'nullable|integer|min:0',
                            'num_of_participants' => 'nullable|integer|min:0',
                            'num_of_barangays' => 'nullable|integer|min:0',
                            'total_volume' => 'nullable|numeric|min:0',
                        ]);
                        break;
                        
                    case 'monthly':
                        $rules = array_merge($rules, [
                            'month' => 'required|string',
                            'year' => 'required|integer|min:2000',
                        ]);
                        break;
                        
                    case 'quarterly':
                        $rules = array_merge($rules, [
                            'quarter' => 'required|integer|min:1|max:4',
                            'year' => 'required|integer|min:2000',
                        ]);
                        break;
                        
                    case 'semestral':
                        $rules = array_merge($rules, [
                            'semester' => 'required|integer|min:1|max:2',
                            'year' => 'required|integer|min:2000',
                        ]);
                        break;
                        
                    case 'annual':
                        $rules = array_merge($rules, [
                            'year' => 'required|integer|min:2000',
                        ]);
                        break;
                }
            }
        }

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'report_type_id.required' => 'Please select a report type.',
            'month.required' => 'Please select a month.',
            'week_number.required' => 'Please enter the week number.',
            'quarter.required' => 'Please select a quarter.',
            'semester.required' => 'Please select a semester.',
            'year.required' => 'Please enter a year.',
            'file.max' => 'The file size must not exceed 10MB.',
        ];
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('year')) {
            $this->merge([
                'year' => (int) $this->year,
            ]);
        }

        if ($this->has('week_number')) {
            $this->merge([
                'week_number' => (int) $this->week_number,
            ]);
        }

        if ($this->has('quarter')) {
            $this->merge([
                'quarter' => (int) $this->quarter,
            ]);
        }

        if ($this->has('semester')) {
            $this->merge([
                'semester' => (int) $this->semester,
            ]);
        }
    }
} 