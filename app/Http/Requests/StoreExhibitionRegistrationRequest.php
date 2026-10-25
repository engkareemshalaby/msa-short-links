<?php

namespace App\Http\Requests;

use App\Models\ExhibitionRegistration;
use App\Support\GeneralRegistrationCertificates;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreExhibitionRegistrationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $iraq = $this->routeIs('crm.iraq.store');
        $general = $this->routeIs('crm.general.store');

        return [
            'registrant_role' => ['required', Rule::in(['parent', 'student'])],
            'student_email' => ['required', 'email', 'max:255'],
            'parent_email' => ['nullable', 'email', 'max:255'],
            'student_name' => ['required', 'string', 'max:255'],
            'student_mobile' => ['required', 'string', 'max:50', 'regex:/^[0-9+()\-\s]{7,50}$/'],
            'parent_mobile' => ['nullable', 'string', 'max:50', 'regex:/^[0-9+()\-\s]{7,50}$/'],
            'certificate_type' => ['required', Rule::in($general
                ? GeneralRegistrationCertificates::certificatesForCategory((string) $this->input('certificate_category'))
                : ($iraq ? ExhibitionRegistration::IRAQ_CERTIFICATES : ExhibitionRegistration::CERTIFICATES))],
            'certificate_category' => $general ? ['required', Rule::in(GeneralRegistrationCertificates::categories())] : ['prohibited'],
            'certificate_type_other' => [$general ? 'prohibited' : 'required_if:certificate_type,Other', 'nullable', 'string', 'max:255'],
            'current_result' => [$iraq || $general ? 'nullable' : 'required', 'string', 'max:255'],
            'interested_faculties' => ['required', 'array', 'min:1', 'max:4'],
            'interested_faculties.*' => ['required', 'distinct', Rule::in(ExhibitionRegistration::FACULTIES)],
            'preferred_contact_method' => [$iraq || $general ? 'prohibited' : 'required', Rule::in(['email', 'whatsapp'])],
            'has_relatives_or_acquaintances_in_egypt' => [$iraq || $general ? 'prohibited' : 'required', 'boolean'],
            'has_accommodation_in_egypt' => [$iraq || $general ? 'prohibited' : 'nullable', 'boolean', ...($iraq || $general ? [] : ['required_if:has_relatives_or_acquaintances_in_egypt,1'])],
            'full_scholarship_eligibility' => ['prohibited'],
            'registration_country' => $general
                ? ['required', Rule::in(GeneralRegistrationCertificates::countries())]
                : ['prohibited'],
            'registration_country_other' => $general ? ['required_if:registration_country,Other', 'nullable', 'string', 'max:120'] : ['prohibited'],
            'consent' => ['accepted'],
            'company_fax' => ['nullable', 'max:0'],
            'client_timezone' => ['nullable', 'string', 'max:100'],
            'screen_width' => ['nullable', 'integer', 'between:1,65535'],
            'screen_height' => ['nullable', 'integer', 'between:1,65535'],
            'viewport_width' => ['nullable', 'integer', 'between:1,65535'],
            'viewport_height' => ['nullable', 'integer', 'between:1,65535'],
            'pixel_ratio' => ['nullable', 'numeric', 'between:0.1,999.99'],
            'hardware_concurrency' => ['nullable', 'integer', 'between:1,65535'],
            'device_memory' => ['nullable', 'numeric', 'between:0.1,999.99'],
            'touch_points' => ['nullable', 'integer', 'between:0,65535'],
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
            'registration_country' => filled($this->input('registration_country')) ? trim((string) $this->input('registration_country')) : null,
            'registration_country_other' => filled($this->input('registration_country_other')) ? trim((string) $this->input('registration_country_other')) : null,
        ]);
    }
}
