<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EventContactStage extends Model
{
    protected $fillable = ['name', 'color', 'position', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'position' => 'integer'];
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(EventContact::class);
    }
}
