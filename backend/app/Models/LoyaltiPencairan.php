<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoyaltiPencairan extends Model
{
    protected $table = 'loyalti_pencairans';
    protected $fillable = ['loyalti_id', 'bukti_transfer', 'status_id'];

    public function loyalti(): BelongsTo { return $this->belongsTo(Loyalti::class, 'loyalti_id'); }
    public function status(): BelongsTo { return $this->belongsTo(Status::class); }
}
