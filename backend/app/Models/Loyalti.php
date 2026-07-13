<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Loyalti extends Model
{
    protected $table = 'loyaltis';
    protected $fillable = [
        'outlet_id', 'periode', 'target_omset', 'target_operasional',
        'omset_aktual', 'capaian_operasional', 'memenuhi_target',
        'jumlah_bonus', 'keterangan', 'status_id',
    ];
    protected function casts(): array
    {
        return [
            'target_omset' => 'decimal:2',
            'target_operasional' => 'decimal:2',
            'omset_aktual' => 'decimal:2',
            'capaian_operasional' => 'decimal:2',
            'memenuhi_target' => 'boolean',
            'jumlah_bonus' => 'decimal:2',
        ];
    }

    public function outlet(): BelongsTo { return $this->belongsTo(Outlet::class); }
    public function status(): BelongsTo { return $this->belongsTo(Status::class); }
    public function pencairans(): HasMany { return $this->hasMany(LoyaltiPencairan::class, 'loyalti_id'); }
}
