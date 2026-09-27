<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class EventContact extends Model
{
    use HasFactory;

    public const STATUSES = ['new', 'contacted', 'qualified', 'closed'];

    protected $fillable = ['import_key', 'name', 'primary_email', 'emails', 'primary_phone', 'phones', 'event_name', 'source', 'status', 'notes', 'raw_data'];

    protected function casts(): array
    {
        return ['emails' => 'array', 'phones' => 'array', 'raw_data' => 'array'];
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(EventContactTag::class, 'event_contact_tag_assignments');
    }
}
