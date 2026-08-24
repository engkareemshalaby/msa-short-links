<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Visit extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'short_link_id', 'visited_at', 'ip_address', 'ip_hash', 'visitor_hash',
        'user_agent', 'referer', 'referer_host', 'device_type', 'browser',
        'platform', 'language', 'query_string', 'is_bot', 'country', 'city',
    ];

    protected function casts(): array
    {
        return ['visited_at' => 'datetime', 'is_bot' => 'boolean'];
    }

    public function shortLink(): BelongsTo
    {
        return $this->belongsTo(ShortLink::class);
    }
}
