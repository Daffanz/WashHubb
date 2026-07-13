<?php
namespace App\Http\Controllers\Api\Operasional;

use App\Http\Controllers\Controller;
use App\Models\JadwalShiftStaf;
use App\Models\JadwalShiftStafDetail;
use App\Models\Status;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class JadwalShiftController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $query = JadwalShiftStaf::with(['outlet', 'user', 'status', 'details.user']);

        if ($outletId = $user->outlets()->first()?->id) {
            $query->where('outlet_id', $outletId);
        }

        // Filter by status
        if ($statusFilter = $request->query('status')) {
            $query->whereHas('status', fn ($q) => $q->where('kode', $statusFilter));
        }

        $jadwals = $query->orderByDesc('minggu_mulai')->paginate(15);

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
            'shifts.*.user_id' => 'required|exists:users,id',
            'shifts.*.hari' => 'required|in:senin,selasa,rabu,kamis,jumat,sabtu,minggu',
            'shifts.*.jam_mulai' => 'required|date_format:H:i',
            'shifts.*.jam_selesai' => 'required|date_format:H:i',
        ]);

        $jadwal = DB::transaction(function () use ($request) {
            $belumBerjalan = Status::where('konteks', 'jadwal_shift_staf')->where('kode', 'belum_berjalan')->first();

            $jadwal = JadwalShiftStaf::create([
                'outlet_id' => $request->outlet_id,
                'user_id' => $request->user()->id,
                'minggu_mulai' => $request->minggu_mulai,
                'status_id' => $belumBerjalan?->id,
            ]);

            foreach ($request->shifts as $shift) {
                JadwalShiftStafDetail::create([
                    'jadwal_shift_staf_id' => $jadwal->id,
                    'user_id' => $shift['user_id'],
                    'hari' => $shift['hari'],
                    'jam_mulai' => $shift['jam_mulai'],
                    'jam_selesai' => $shift['jam_selesai'],
                ]);
            }

            return $jadwal;
        });

        return response()->json([
            'message' => 'Jadwal shift berhasil dibuat.',
            'data' => $this->format($jadwal->fresh()->load(['outlet', 'user', 'status', 'details.user'])),
        ], 201);
    }

    public function show(JadwalShiftStaf $jadwal): JsonResponse
    {
        $jadwal->load(['outlet', 'user', 'status', 'details.user']);
        return response()->json(['data' => $this->format($jadwal)]);
    }

    public function update(Request $request, JadwalShiftStaf $jadwal): JsonResponse
    {
        $belumBerjalan = Status::where('konteks', 'jadwal_shift_staf')->where('kode', 'belum_berjalan')->first();
        if ($jadwal->status_id !== $belumBerjalan?->id) {
            return response()->json(['message' => 'Hanya bisa diedit saat status belum berjalan.'], 422);
        }

        $request->validate([
            'shifts' => 'required|array|min:1',
            'shifts.*.user_id' => 'required|exists:users,id',
            'shifts.*.hari' => 'required|in:senin,selasa,rabu,kamis,jumat,sabtu,minggu',
            'shifts.*.jam_mulai' => 'required|date_format:H:i',
            'shifts.*.jam_selesai' => 'required|date_format:H:i',
        ]);

        DB::transaction(function () use ($request, $jadwal) {
            $jadwal->details()->delete();
            foreach ($request->shifts as $shift) {
                JadwalShiftStafDetail::create([
                    'jadwal_shift_staf_id' => $jadwal->id,
                    'user_id' => $shift['user_id'],
                    'hari' => $shift['hari'],
                    'jam_mulai' => $shift['jam_mulai'],
                    'jam_selesai' => $shift['jam_selesai'],
                ]);
            }
        });

        return response()->json(['message' => 'Jadwal shift berhasil diperbarui.']);
    }

    public function history(Request $request): JsonResponse
    {
        $user = $request->user();
        $query = JadwalShiftStaf::with(['outlet', 'user', 'status', 'details.user'])
            ->whereHas('status', fn ($q) => $q->where('kode', 'selesai'));

        if ($outletId = $user->outlets()->first()?->id) {
            $query->where('outlet_id', $outletId);
        }

        $jadwals = $query->orderByDesc('minggu_mulai')->paginate(15);

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

    private function format($j): array
    {
        return [
            'id' => $j->id,
            'outlet' => $j->outlet ? ['id' => $j->outlet->id, 'nama' => $j->outlet->nama] : null,
            'user' => $j->user ? ['id' => $j->user->id, 'nama' => $j->user->nama] : null,
            'minggu_mulai' => $j->minggu_mulai?->format('Y-m-d'),
            'status' => $j->status ? ['id' => $j->status->id, 'kode' => $j->status->kode, 'label' => $j->status->label] : null,
            'details' => $j->details->map(fn ($d) => [
                'id' => $d->id,
                'user' => $d->user ? ['id' => $d->user->id, 'nama' => $d->user->nama] : null,
                'hari' => $d->hari,
                'jam_mulai' => $d->jam_mulai,
                'jam_selesai' => $d->jam_selesai,
            ]),
            'created_at' => $j->created_at?->toDateTimeString(),
        ];
    }
}
