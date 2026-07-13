<?php
namespace App\Http\Controllers\Api\Operasional;

use App\Http\Controllers\Controller;
use App\Models\JadwalShiftStaf;
use App\Models\JadwalShiftStafDetail;
use App\Models\Status;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class JadwalShiftController extends Controller
{
    // Mapping hari Indonesia ke Carbon day name
    private const HARI_MAP = [
        'senin' => 'Monday',
        'selasa' => 'Tuesday',
        'rabu' => 'Wednesday',
        'kamis' => 'Thursday',
        'jumat' => 'Friday',
        'sabtu' => 'Saturday',
        'minggu' => 'Sunday',
    ];

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $query = JadwalShiftStaf::with(['outlet', 'user', 'details.status']);

        if ($outletId = $user->outlets()->first()?->id) {
            $query->where('outlet_id', $outletId);
        }

        $jadwals = $query->orderByDesc('minggu_mulai')->paginate(15);

        // Update status setiap detail berdasarkan jam sekarang
        foreach ($jadwals as $jadwal) {
            $this->updateDetailStatuses($jadwal);
        }

        return response()->json([
            'data' => $jadwals->map(fn ($j) => $this->format($j)),
            'meta' => [
                'current_page' => $jadwals->currentPage(),
                'last_page' => $jadwals->lastPage(),
                'per_page' => $jadwals->perPage(),
                'total' => $jadwals->total(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'outlet_id' => 'required|exists:outlets,id',
            'minggu_mulai' => 'required|date',
            'shifts' => 'required|array|min:1',
            'shifts.*.nama_karyawan' => 'required|string|max:255',
            'shifts.*.hari' => 'required|in:senin,selasa,rabu,kamis,jumat,sabtu,minggu',
            'shifts.*.jam_mulai' => 'required|date_format:H:i',
            'shifts.*.jam_selesai' => 'required|date_format:H:i',
        ]);

        $now = Carbon::now('Asia/Jakarta');
        $hariIni = $now->dayName;
        $jamSekarang = $now->format('H:i');

        $jadwal = DB::transaction(function () use ($request, $hariIni, $jamSekarang) {
            $jadwal = JadwalShiftStaf::create([
                'outlet_id' => $request->outlet_id,
                'user_id' => $request->user()->id,
                'minggu_mulai' => $request->minggu_mulai,
            ]);

            foreach ($request->shifts as $shift) {
                // Hitung status berdasarkan jam sekarang
                $kodeStatus = $this->hitungStatusSaatCreate($shift['hari'], $shift['jam_mulai'], $shift['jam_selesai'], $hariIni, $jamSekarang);
                $status = Status::where('konteks', 'jadwal_shift_staf_detail')->where('kode', $kodeStatus)->first();

                JadwalShiftStafDetail::create([
                    'jadwal_shift_staf_id' => $jadwal->id,
                    'nama_karyawan' => $shift['nama_karyawan'],
                    'hari' => $shift['hari'],
                    'jam_mulai' => $shift['jam_mulai'],
                    'jam_selesai' => $shift['jam_selesai'],
                    'status_id' => $status?->id,
                ]);
            }

            return $jadwal;
        });

        return response()->json([
            'message' => 'Jadwal shift berhasil dibuat.',
            'data' => $this->format($jadwal->fresh()->load(['outlet', 'user', 'details.status'])),
        ], 201);
    }

    public function show(JadwalShiftStaf $jadwal): JsonResponse
    {
        $jadwal->load(['outlet', 'user', 'details.status']);
        $this->updateDetailStatuses($jadwal);
        return response()->json(['data' => $this->format($jadwal)]);
    }

    public function update(Request $request, JadwalShiftStaf $jadwal): JsonResponse
    {
        $request->validate([
            'shifts' => 'required|array|min:1',
            'shifts.*.nama_karyawan' => 'required|string|max:255',
            'shifts.*.hari' => 'required|in:senin,selasa,rabu,kamis,jumat,sabtu,minggu',
            'shifts.*.jam_mulai' => 'required|date_format:H:i',
            'shifts.*.jam_selesai' => 'required|date_format:H:i',
        ]);

        $belumBerjalan = Status::where('konteks', 'jadwal_shift_staf_detail')->where('kode', 'belum_berjalan')->first();

        DB::transaction(function () use ($request, $jadwal, $belumBerjalan) {
            $jadwal->details()->delete();
            foreach ($request->shifts as $shift) {
                JadwalShiftStafDetail::create([
                    'jadwal_shift_staf_id' => $jadwal->id,
                    'nama_karyawan' => $shift['nama_karyawan'],
                    'hari' => $shift['hari'],
                    'jam_mulai' => $shift['jam_mulai'],
                    'jam_selesai' => $shift['jam_selesai'],
                    'status_id' => $belumBerjalan?->id,
                ]);
            }
        });

        return response()->json(['message' => 'Jadwal shift berhasil diperbarui.']);
    }

    public function history(Request $request): JsonResponse
    {
        $user = $request->user();
        $query = JadwalShiftStaf::with(['outlet', 'user', 'details.status']);

        if ($outletId = $user->outlets()->first()?->id) {
            $query->where('outlet_id', $outletId);
        }

        $jadwals = $query->orderByDesc('minggu_mulai')->paginate(15);

        // Update status dan filter hanya yang semua detail sudah selesai
        $filtered = $jadwals->getCollection()->filter(function ($j) {
            $this->updateDetailStatuses($j);
            return $j->details->every(fn ($d) => $d->status?->kode === 'selesai');
        });

        $jadwals->setCollection($filtered);

        return response()->json([
            'data' => $jadwals->map(fn ($j) => $this->format($j)),
            'meta' => [
                'current_page' => $jadwals->currentPage(),
                'last_page' => $jadwals->lastPage(),
                'per_page' => $jadwals->perPage(),
                'total' => $jadwals->total(),
            ],
        ]);
    }

    /**
     * Update status setiap shift detail berdasarkan jam sekarang (WIB)
     */
    private function updateDetailStatuses(JadwalShiftStaf $jadwal): void
    {
        $now = Carbon::now('Asia/Jakarta');
        $hariIni = $now->dayName; // e.g., "Monday"
        $jamSekarang = $now->format('H:i');

        foreach ($jadwal->details as $detail) {
            $kodeStatus = $this->hitungStatusDetail($detail, $hariIni, $jamSekarang, $jadwal->minggu_mulai);
            $status = Status::where('konteks', 'jadwal_shift_staf_detail')->where('kode', $kodeStatus)->first();

            if ($status && $detail->status_id !== $status->id) {
                $detail->update(['status_id' => $status->id]);
                $detail->setRelation('status', $status);
            }
        }
    }

    /**
     * Hitung status per detail berdasarkan jam
     */
    private function hitungStatusDetail(JadwalShiftStafDetail $detail, string $hariIni, string $jamSekarang, $mingguMulai): string
    {
        $hariShift = self::HARI_MAP[$detail->hari] ?? null;

        // Jika hari ini adalah hari shift
        if ($hariShift === $hariIni) {
            $jamMulai = substr($detail->jam_mulai, 0, 5);
            $jamSelesai = substr($detail->jam_selesai, 0, 5);

            if ($jamSekarang < $jamMulai) {
                return 'belum_berjalan';
            } elseif ($jamSekarang >= $jamMulai && $jamSekarang <= $jamSelesai) {
                return 'berjalan';
            } else {
                return 'selesai';
            }
        }

        // Jika hari shift sudah lewat dari hari ini
        $dayOrder = ['Monday' => 1, 'Tuesday' => 2, 'Wednesday' => 3, 'Thursday' => 4, 'Friday' => 5, 'Saturday' => 6, 'Sunday' => 7];
        $hariShiftNum = $dayOrder[$hariShift] ?? 0;
        $hariIniNum = $dayOrder[$hariIni] ?? 0;

        if ($hariShiftNum < $hariIniNum) {
            return 'selesai';
        }

        return 'belum_berjalan';
    }

    /**
     * Hitung status saat create berdasarkan jam sekarang
     */
    private function hitungStatusSaatCreate(string $hari, string $jamMulai, string $jamSelesai, string $hariIni, string $jamSekarang): string
    {
        $hariShift = self::HARI_MAP[$hari] ?? null;

        // Jika hari ini adalah hari shift
        if ($hariShift === $hariIni) {
            $jamMulaiFormatted = substr($jamMulai, 0, 5);
            $jamSelesaiFormatted = substr($jamSelesai, 0, 5);

            if ($jamSekarang < $jamMulaiFormatted) {
                return 'belum_berjalan';
            } elseif ($jamSekarang >= $jamMulaiFormatted && $jamSekarang <= $jamSelesaiFormatted) {
                return 'berjalan';
            } else {
                return 'selesai';
            }
        }

        // Jika hari shift sudah lewat dari hari ini
        $dayOrder = ['Monday' => 1, 'Tuesday' => 2, 'Wednesday' => 3, 'Thursday' => 4, 'Friday' => 5, 'Saturday' => 6, 'Sunday' => 7];
        $hariShiftNum = $dayOrder[$hariShift] ?? 0;
        $hariIniNum = $dayOrder[$hariIni] ?? 0;

        if ($hariShiftNum < $hariIniNum) {
            return 'selesai';
        }

        return 'belum_berjalan';
    }

    private function format($j): array
    {
        return [
            'id' => $j->id,
            'outlet' => $j->outlet ? ['id' => $j->outlet->id, 'nama' => $j->outlet->nama] : null,
            'user' => $j->user ? ['id' => $j->user->id, 'nama' => $j->user->nama] : null,
            'minggu_mulai' => $j->minggu_mulai?->format('Y-m-d'),
            'details' => $j->details->map(fn ($d) => [
                'id' => $d->id,
                'nama_karyawan' => $d->nama_karyawan,
                'hari' => $d->hari,
                'jam_mulai' => $d->jam_mulai,
                'jam_selesai' => $d->jam_selesai,
                'status' => $d->status ? ['id' => $d->status->id, 'kode' => $d->status->kode, 'label' => $d->status->label] : null,
            ]),
            'created_at' => $j->created_at?->toDateTimeString(),
        ];
    }
}
