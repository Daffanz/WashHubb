<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JadwalShiftStaf extends Model
{
    protected $table = 'jadwal_shift_stafs';
    protected $fillable = ['outlet_id', 'user_id', 'minggu_mulai', 'status_id'];
    protected function casts(): array { return ['minggu_mulai' => 'date']; }

    public function outlet(): BelongsTo { return $this->belongsTo(Outlet::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function status(): BelongsTo { return $this->belongsTo(Status::class); }
    public function details(): HasMany { return $this->hasMany(JadwalShiftStafDetail::class, 'jadwal_shift_staf_id'); }
}
