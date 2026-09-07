<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentReferral extends Model
{
    use HasFactory;

    public const STATUSES = ['new', 'contacted', 'enrolled', 'incomplete'];

    public const PROGRAMS = [
        'Dentistry', 'Pharmacy', 'Biotechnology', 'Engineering',
        'Computer Science', 'Arts & Design', 'Management Sciences',
        'Languages', 'Other',
    ];

    protected $fillable = [
        'recruitment_partner_id', 'reference_code', 'student_name', 'mobile',
        'nationality', 'desired_program', 'passport_path', 'consent', 'status', 'ip_hash',
    ];

    protected function casts(): array
    {
        return ['consent' => 'boolean'];
    }

    public function partner(): BelongsTo
    {
        return $this->belongsTo(RecruitmentPartner::class, 'recruitment_partner_id');
    }
}
