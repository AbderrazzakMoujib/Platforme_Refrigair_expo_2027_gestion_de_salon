<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class Badge extends Model
{
    protected $fillable = ['exhibitor_id','first_name','last_name','function','email','phone'];
    public function exhibitor(): BelongsTo { return $this->belongsTo(Exhibitor::class); }
}
