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
            'country' => ['required', 'string', 'max:120'],
            'city' => ['nullable', 'string', 'max:120'],
            'website' => ['nullable', 'url:http,https', 'max:500'],
            'contact_name' => ['required', 'string', 'max:255'],
            'job_title' => ['nullable', 'string', 'max:150'],
            'mobile' => ['required', 'string', 'max:50', 'regex:/^[0-9+()\-\s]{7,50}$/'],
            'email' => ['required', 'email:rfc', 'max:255'],
            'recruitment_countries' => ['required', 'string', 'max:2000'],
            'annual_students_range' => ['required', Rule::in(['1-25', '26-50', '51-100', '101-250', '251+'])],
            'works_with_egyptian_universities' => ['required', 'boolean'],
            'current_universities' => ['nullable', 'required_if:works_with_egyptian_universities,1', 'string', 'max:3000'],
            'expected_msa_students_range' => ['required', Rule::in(['1-10', '11-25', '26-50', '51-100', '101+'])],
            'interested_programs' => ['required', 'array', 'min:1'],
            'interested_programs.*' => ['string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'commission_type' => ['required', Rule::in(['fixed_usd', 'percentage'])],
            'commission_value' => ['required', 'numeric', 'min:0', 'max:1000000'],
            'commission_basis' => ['nullable', 'required_if:commission_type,percentage', Rule::in(['installment', 'academic_year'])],
            'exclusive_discount_percent' => ['required', 'numeric', 'between:0,100'],
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

            $countries = preg_split('/[,،\n]+/u', (string) $this->input('recruitment_countries'));
            if (! collect($countries)->contains(fn (string $country) => filled(trim($country)))) {
                $validator->errors()->add('recruitment_countries', __('Enter at least one recruitment country.'));
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
}
