<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Mesin extends Model
{
    protected $table = 'mesins';
    protected $fillable = ['nama', 'kode_mesin', 'merk', 'tipe', 'kapasitas', 'harga_standar', 'status_id'];
    protected function casts(): array { return ['kapasitas' => 'integer', 'harga_standar' => 'decimal:2']; }

    public function status(): BelongsTo { return $this->belongsTo(Status::class); }
}
