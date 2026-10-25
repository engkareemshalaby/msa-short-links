<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExhibitionRegistration extends Model
{
    use HasFactory;

    public const STATUSES = ['new', 'contacted', 'qualified', 'applied', 'closed'];

    public const CERTIFICATES = [
        'WAEC / WASSCE', 'NECO / SSCE', 'NABTEB', 'Cambridge IGCSE',
        'International Baccalaureate (IB Diploma)', 'French BaccalaurÃ©at',
        'Pearson Edexcel International (GCSE or A Level)', 'Other',
    ];

    public const IRAQ_CERTIFICATES = [
        'Iraqi Secondary School Certificate (Sixth Preparatory)',
        'Cambridge IGCSE / A Level',
        'Pearson Edexcel International GCSE / A Level',
        'American High School Diploma',
        'International Baccalaureate (IB Diploma)',
        'French Baccalaureate',
        'German Abitur',
        'Other',
    ];

    public const FACULTIES = [
        'Faculty of Dentistry', 'Faculty of Pharmacy', 'Faculty of Physical Therapy',
        'Faculty of Biotechnology', 'Faculty of Nutrition and Food Technology',
        'Faculty of Engineering', 'Faculty of Computer Science',
        'Faculty of Management Sciences', 'Faculty of Mass Communication',
        'Faculty of Arts and Design', 'Faculty of Languages',
    ];

    protected $fillable = [
        'reference_code', 'registrant_role', 'student_email', 'parent_email',
        'student_name', 'student_mobile', 'parent_mobile', 'certificate_type',
        'certificate_type_other', 'current_result', 'interested_faculties',
        'preferred_contact_method', 'exhibition_location', 'registration_country', 'status', 'ip_hash',
        'has_relatives_or_acquaintances_in_egypt', 'has_accommodation_in_egypt',
        'full_scholarship_eligibility',
        'source_short_link_id', 'source_visit_id', 'source_short_code', 'source_landing_url',
        'source_referrer', 'source_form_locale', 'source_ref', 'source_bref', 'source_sem', 'source_utm_source',
        'source_utm_medium', 'source_utm_campaign', 'source_utm_content',
    ];

    protected function casts(): array
    {
        return [
            'interested_faculties' => 'array',
            'has_relatives_or_acquaintances_in_egypt' => 'boolean',
            'has_accommodation_in_egypt' => 'boolean',
        ];
    }

    public function sourceShortLink(): BelongsTo
    {
        return $this->belongsTo(ShortLink::class, 'source_short_link_id')->withTrashed();
    }
}
