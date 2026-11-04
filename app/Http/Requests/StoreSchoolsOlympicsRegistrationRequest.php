<?php

namespace App\Http\Requests;

use App\Models\ExhibitionRegistration;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSchoolsOlympicsRegistrationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $phone = ['string', 'max:50', 'regex:/^[0-9+()\-\s]{7,50}$/'];

        return [
            'student_email' => ['required', 'email', 'max:255'],
            'registrant_role' => ['required', Rule::in(['parent', 'student'])],
            'student_name' => ['required', 'string', 'max:255'],
            'student_mobile' => ['required', ...$phone],
            'whatsapp_number' => ['nullable', ...$phone],
            'parent_mobile' => ['required', ...$phone],
            'certificate_type' => ['required', Rule::in(ExhibitionRegistration::SCHOOLS_OLYMPICS_CERTIFICATES)],
            'interested_faculties' => ['required', 'array', 'min:1', 'max:4'],
            'interested_faculties.*' => ['required', 'distinct', Rule::in(ExhibitionRegistration::FACULTIES)],
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
        app()->setLocale($this->routeIs('crm.schools-olympics.store.ar') ? 'ar' : 'en');
        $this->merge([
            'student_email' => mb_strtolower(trim((string) $this->input('student_email'))),
            'student_name' => trim((string) $this->input('student_name')),
            'student_mobile' => trim((string) $this->input('student_mobile')),
            'whatsapp_number' => filled($this->input('whatsapp_number')) ? trim((string) $this->input('whatsapp_number')) : null,
            'parent_mobile' => trim((string) $this->input('parent_mobile')),
        ]);
    }
}
