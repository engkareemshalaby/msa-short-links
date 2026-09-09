<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RecruitmentPartner extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'crm_submission_id', 'name', 'code', 'access_token', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function studentReferrals(): HasMany
    {
        return $this->hasMany(StudentReferral::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function crmSubmission(): BelongsTo
    {
        return $this->belongsTo(CrmSubmission::class);
    }
}
