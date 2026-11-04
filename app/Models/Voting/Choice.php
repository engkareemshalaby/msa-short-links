<?php

namespace App\Models\Voting;

use Illuminate\Database\Eloquent\Model;

class Choice extends Model
{
    protected $table = 'voting_choices';
    public $timestamps = false;
}
