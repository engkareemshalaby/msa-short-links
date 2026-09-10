<?php

namespace App\Http\Requests;

use App\Models\StudentReferral;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreStudentReferralRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'student_name' => ['required', 'string', 'max:255'],
            'mobile' => ['required', 'string', 'max:50', 'regex:/^[0-9+()\-\s]{7,50}$/'],
            'email' => ['nullable', 'email', 'max:255'],
            'nationality' => ['required', Rule::in([...StudentReferral::COUNTRIES, 'Egyptian'])],
            'desired_program' => ['required', Rule::in(StudentReferral::PROGRAMS)],
            'passport' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            'consent' => ['accepted'],
            'company_fax' => ['nullable', 'max:0'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'student_name' => trim((string) $this->input('student_name')),
            'mobile' => trim((string) $this->input('mobile')),
            'nationality' => trim((string) $this->input('nationality')),
            'email' => filled($this->input('email')) ? mb_strtolower(trim((string) $this->input('email'))) : null,
        ]);
    }
}
