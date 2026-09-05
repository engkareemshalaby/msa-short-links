<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CrmSubmission extends Model
{
    use HasFactory;

    public const STATUSES = ['new', 'reviewed', 'archived'];

    protected $fillable = [
        'agency_name', 'country', 'city', 'website', 'contact_name', 'job_title',
        'mobile', 'email', 'recruitment_countries', 'annual_students_range',
        'works_with_egyptian_universities', 'current_universities',
        'expected_msa_students_range', 'interested_programs', 'notes',
        'commission_type', 'commission_value', 'commission_basis',
        'exclusive_discount_percent', 'consent', 'status', 'reviewed_at',
        'source', 'ip_hash', 'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'recruitment_countries' => 'array',
            'interested_programs' => 'array',
            'works_with_egyptian_universities' => 'boolean',
            'consent' => 'boolean',
            'commission_value' => 'decimal:2',
            'exclusive_discount_percent' => 'decimal:2',
            'reviewed_at' => 'datetime',
        ];
    }
}
