<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model; use Illuminate\Database\Eloquent\Relations\BelongsTo; use Illuminate\Database\Eloquent\Relations\HasMany;
class Campaign extends Model { protected $fillable=['name','description','utm_source','utm_medium','utm_campaign','is_active','created_by']; protected function casts(): array{return ['is_active'=>'boolean'];} public function links():HasMany{return $this->hasMany(ShortLink::class);} public function creator():BelongsTo{return $this->belongsTo(User::class,'created_by');} }
