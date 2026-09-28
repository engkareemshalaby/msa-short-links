<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class EventContact extends Model
{
    use HasFactory;

    public const STATUSES = ['new', 'contacted', 'qualified', 'closed'];

    protected $fillable = ['import_key', 'name', 'organization_name', 'job_title', 'primary_email', 'emails', 'primary_phone', 'phones', 'event_name', 'source', 'status', 'event_contact_stage_id', 'notes', 'extra_data', 'raw_data'];

    protected function casts(): array
    {
        return ['emails' => 'array', 'phones' => 'array', 'extra_data' => 'array', 'raw_data' => 'array'];
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(EventContactTag::class, 'event_contact_tag_assignments');
    }

    public function stage(): BelongsTo
    {
        return $this->belongsTo(EventContactStage::class, 'event_contact_stage_id');
    }
}
