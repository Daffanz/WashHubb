<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PenerimaanBarang extends Model
{
    use SoftDeletes;

    protected $table = 'penerimaan_barangs';

    protected $fillable = [
        'distribusi_barang_id',
        'tanggal_terima',
        'total_bayar',
        'catatan',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_terima' => 'date',
            'total_bayar'    => 'decimal:2',
        ];
    }

    public function distribusiBarang(): BelongsTo
    {
        return $this->belongsTo(DistribusiBarang::class, 'distribusi_barang_id');
    }
}
