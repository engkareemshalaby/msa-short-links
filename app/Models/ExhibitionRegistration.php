<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExhibitionRegistration extends Model
{
    use HasFactory;

    public const STATUSES = ['new', 'contacted', 'qualified', 'applied', 'closed'];

    public const CERTIFICATES = [
        'WAEC / WASSCE', 'NECO / SSCE', 'NABTEB', 'Cambridge IGCSE',
        'International Baccalaureate (IB Diploma)', 'French Baccalauréat',
        'Pearson Edexcel International (GCSE or A Level)', 'Other',
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
        'preferred_contact_method', 'exhibition_location', 'status', 'ip_hash',
    ];

    protected function casts(): array
    {
        return ['interested_faculties' => 'array'];
    }
}
