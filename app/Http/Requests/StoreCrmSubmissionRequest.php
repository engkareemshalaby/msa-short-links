<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreCrmSubmissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'agency_name' => ['required', 'string', 'max:255'],
            'country' => ['required', 'string', Rule::in($this->recruitmentCountries())],
            'city' => ['nullable', 'string', 'max:120'],
            'website' => ['nullable', 'url:http,https', 'max:500'],
            'contact_name' => ['required', 'string', 'max:255'],
            'job_title' => ['nullable', 'string', 'max:150'],
            'mobile' => ['required', 'string', 'max:50', 'regex:/^[0-9+()\-\s]{7,50}$/'],
            'email' => ['required', 'email:rfc', 'max:255'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'recruitment_countries' => ['required', 'array', 'min:1'],
            'recruitment_countries.*' => ['string', Rule::in($this->recruitmentCountries())],
            'annual_students_range' => ['required', Rule::in(['1-25', '26-50', '51-100', '101-250', '251+'])],
            'works_with_egyptian_universities' => ['required', 'boolean'],
            'current_universities' => ['nullable', 'required_if:works_with_egyptian_universities,1', 'string', 'max:3000'],
            'expected_msa_students_range' => ['required', Rule::in(['1-10', '11-25', '26-50', '51-100', '101+'])],
            'interested_programs' => ['nullable', 'array'],
            'interested_programs.*' => ['string', Rule::in($this->programs())],
            'notes' => ['nullable', 'string', 'max:5000'],
            'commission_type' => ['required', Rule::in(['percentage'])],
            'commission_value' => ['required', 'numeric', 'min:0', 'max:1000000'],
            'commission_basis' => ['nullable', 'required_if:commission_type,percentage', Rule::in(['installment', 'academic_year'])],
            'exclusive_discount_percent' => ['required', 'numeric', 'between:0,30'],
            'consent' => ['accepted'],
            'source' => ['nullable', 'string', 'max:100'],
            'company_fax' => ['nullable', 'max:0'],
        ];
    }

    public function after(): array
    {
        return [function (Validator $validator): void {
            if ($this->input('commission_type') === 'percentage' && (float) $this->input('commission_value') > 100) {
                $validator->errors()->add('commission_value', __('A percentage cannot be greater than 100.'));
            }

        }];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'agency_name' => trim((string) $this->input('agency_name')),
            'country' => trim((string) $this->input('country')),
            'mobile' => trim((string) $this->input('mobile')),
            'email' => mb_strtolower(trim((string) $this->input('email'))),
            'source' => $this->filled('source') ? trim((string) $this->input('source')) : 'direct',
        ]);
    }

    private function recruitmentCountries(): array
    {
        return ['Egypt', 'Saudi Arabia', 'United Arab Emirates', 'Qatar', 'Kuwait', 'Jordan', 'Oman', 'Bahrain', 'Iraq', 'Palestine', 'Lebanon', 'Syria', 'Libya', 'Sudan', 'Yemen', 'Algeria', 'Morocco', 'Tunisia'];
    }

    private function programs(): array
    {
        return ['Dentistry', 'Pharmacy', 'Biotechnology', 'Engineering', 'Computer Science', 'Arts & Design', 'Management Sciences', 'Languages', 'Other'];
    }
}
