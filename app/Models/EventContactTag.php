<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class EventContactTag extends Model
{
    protected $fillable = ['name', 'color'];

    public function contacts(): BelongsToMany
    {
        return $this->belongsToMany(EventContact::class, 'event_contact_tag_assignments');
    }
}
