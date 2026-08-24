<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model; use Illuminate\Database\Eloquent\Relations\BelongsTo;
class ApiKey extends Model { protected $fillable=['name','key_hash','prefix','user_id','last_used_at','expires_at']; protected function casts():array{return ['last_used_at'=>'datetime','expires_at'=>'datetime'];} public function user():BelongsTo{return $this->belongsTo(User::class);} }
