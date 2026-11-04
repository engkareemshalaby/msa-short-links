<?php

namespace App\Models\Voting;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Member extends Model
{
    protected $table = 'voting_members';
    protected $fillable = ['user_id', 'staff_id'];

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function votesReceived(): HasMany { return $this->hasMany(Choice::class, 'candidate_id'); }
}
