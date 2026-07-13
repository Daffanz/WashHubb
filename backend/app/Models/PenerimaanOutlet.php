<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PenerimaanOutlet extends Model
{
    protected $table = 'penerimaan_outlets';
    protected $fillable = ['distribusi_outlet_id', 'user_id', 'tanggal_terima'];
    protected function casts(): array { return ['tanggal_terima' => 'date']; }

    public function distribusiOutlet(): BelongsTo { return $this->belongsTo(DistribusiOutlet::class, 'distribusi_outlet_id'); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function details(): HasMany { return $this->hasMany(PenerimaanOutletDetail::class, 'penerimaan_outlet_id'); }
}
