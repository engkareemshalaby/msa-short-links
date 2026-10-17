<?php

namespace App\Http\Requests;

use App\Models\ExhibitionRegistration;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreExhibitionRegistrationRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $iraq = $this->routeIs('crm.iraq.store');
        return [
            'registrant_role' => ['required', Rule::in(['parent', 'student'])],
            'student_email' => ['required', 'email', 'max:255'],
            'parent_email' => ['nullable', 'email', 'max:255'],
            'student_name' => ['required', 'string', 'max:255'],
            'student_mobile' => ['required', 'string', 'max:50', 'regex:/^[0-9+()\-\s]{7,50}$/'],
            'parent_mobile' => ['nullable', 'string', 'max:50', 'regex:/^[0-9+()\-\s]{7,50}$/'],
            'certificate_type' => ['required', Rule::in($iraq ? ExhibitionRegistration::IRAQ_CERTIFICATES : ExhibitionRegistration::CERTIFICATES)],
            'certificate_type_other' => ['required_if:certificate_type,Other', 'nullable', 'string', 'max:255'],
            'current_result' => [$iraq ? 'nullable' : 'required', 'string', 'max:255'],
            'interested_faculties' => ['required', 'array', 'min:1', 'max:4'],
            'interested_faculties.*' => ['required', 'distinct', Rule::in(ExhibitionRegistration::FACULTIES)],
            'preferred_contact_method' => [$iraq ? 'prohibited' : 'required', Rule::in(['email', 'whatsapp'])],
            'has_relatives_or_acquaintances_in_egypt' => [$iraq ? 'prohibited' : 'required', 'boolean'],
            'has_accommodation_in_egypt' => [$iraq ? 'prohibited' : 'nullable', 'boolean', ...($iraq ? [] : ['required_if:has_relatives_or_acquaintances_in_egypt,1'])],
            'full_scholarship_eligibility' => ['prohibited'],
            'consent' => ['accepted'],
            'company_fax' => ['nullable', 'max:0'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'student_name' => trim((string) $this->input('student_name')),
            'student_email' => mb_strtolower(trim((string) $this->input('student_email'))),
            'parent_email' => filled($this->input('parent_email')) ? mb_strtolower(trim((string) $this->input('parent_email'))) : null,
            'student_mobile' => trim((string) $this->input('student_mobile')),
            'parent_mobile' => filled($this->input('parent_mobile')) ? trim((string) $this->input('parent_mobile')) : null,
        ]);
    }
}


