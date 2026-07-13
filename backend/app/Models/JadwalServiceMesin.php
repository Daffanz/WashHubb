<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JadwalServiceMesin extends Model
{
    protected $table = 'jadwal_service_mesins';
    protected $fillable = [
        'mesin_id', 'outlet_id', 'user_id', 'tanggal_pengajuan',
        'tanggal_service', 'deskripsi', 'menunggu_mesin_bebas', 'status_id',
    ];
    protected function casts(): array
    {
        return [
            'tanggal_pengajuan' => 'date',
            'tanggal_service' => 'date',
            'menunggu_mesin_bebas' => 'boolean',
        ];
    }

    public function mesin(): BelongsTo { return $this->belongsTo(Mesin::class); }
    public function outlet(): BelongsTo { return $this->belongsTo(Outlet::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function status(): BelongsTo { return $this->belongsTo(Status::class); }
}
