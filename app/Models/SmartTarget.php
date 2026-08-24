<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model; use Illuminate\Database\Eloquent\Relations\BelongsTo;
class SmartTarget extends Model { protected $fillable=['short_link_id','name','destination_url','condition_type','condition_value','priority','is_active']; protected function casts():array{return ['is_active'=>'boolean'];} public function link():BelongsTo{return $this->belongsTo(ShortLink::class,'short_link_id');} }
