<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JadwalShiftStafDetail extends Model
{
    protected $table = 'jadwal_shift_staf_details';
    protected $fillable = ['jadwal_shift_staf_id', 'user_id', 'hari', 'jam_mulai', 'jam_selesai'];
    protected function casts(): array { return ['jam_mulai' => 'datetime:H:i', 'jam_selesai' => 'datetime:H:i']; }

    public function jadwalShiftStaf(): BelongsTo { return $this->belongsTo(JadwalShiftStaf::class, 'jadwal_shift_staf_id'); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
}
