<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model; use Illuminate\Database\Eloquent\Relations\BelongsTo; use Illuminate\Database\Eloquent\Relations\BelongsToMany;
class RetargetingPixel extends Model { protected $fillable=['name','provider','snippet','is_active','created_by']; protected function casts():array{return ['is_active'=>'boolean'];} public function links():BelongsToMany{return $this->belongsToMany(ShortLink::class);} public function creator():BelongsTo{return $this->belongsTo(User::class,'created_by');} }
