<?php

namespace App\Models\Voting;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $table = 'voting_settings';
    protected $fillable = ['is_open', 'show_results_to_members'];
    protected function casts(): array { return ['is_open' => 'boolean', 'show_results_to_members' => 'boolean']; }
}
