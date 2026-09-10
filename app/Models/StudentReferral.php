<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentReferral extends Model
{
    use HasFactory;

    public const STATUSES = ['new', 'contacted', 'applied_on_study_in_egypt', 'enrolled', 'incomplete'];

    public const PROGRAMS = [
        'Dentistry', 'Pharmacy', 'Biotechnology', 'Engineering',
        'Computer Science', 'Arts & Design', 'Management Sciences',
        'Languages', 'Other',
    ];

    public const COUNTRIES = [
        'Egypt', 'Saudi Arabia', 'United Arab Emirates', 'Qatar', 'Kuwait', 'Jordan',
        'Oman', 'Bahrain', 'Iraq', 'Palestine', 'Lebanon', 'Syria', 'Libya', 'Sudan',
        'Yemen', 'Algeria', 'Morocco', 'Tunisia', 'Other',
    ];

    protected $fillable = [
        'recruitment_partner_id', 'reference_code', 'student_name', 'mobile',
        'email', 'nationality', 'desired_program', 'passport_path', 'consent', 'status', 'ip_hash',
        'study_in_egypt_applied', 'study_in_egypt_updated_by', 'study_in_egypt_updated_at',
    ];

    protected function casts(): array
    {
        return [
            'consent' => 'boolean',
            'study_in_egypt_applied' => 'boolean',
            'study_in_egypt_updated_at' => 'datetime',
        ];
    }

    public function partner(): BelongsTo
    {
        return $this->belongsTo(RecruitmentPartner::class, 'recruitment_partner_id');
    }

    public function studyInEgyptUpdatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'study_in_egypt_updated_by');
    }
}
