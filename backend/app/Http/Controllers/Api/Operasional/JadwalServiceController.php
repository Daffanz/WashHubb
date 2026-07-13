<?php
namespace App\Http\Controllers\Api\Operasional;

use App\Http\Controllers\Controller;
use App\Models\JadwalServiceMesin;
use App\Models\Mesin;
use App\Models\Status;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class JadwalServiceController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $query = JadwalServiceMesin::with(['mesin', 'outlet', 'user', 'status']);

        if ($outletId = $user->outlets()->first()?->id) {
            $query->where('outlet_id', $outletId);
        }

        $jadwals = $query->orderByDesc('created_at')->paginate(15);

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
            'mesin_id' => 'required|exists:mesins,id',
            'outlet_id' => 'required|exists:outlets,id',
            'deskripsi' => 'required|string',
        ]);

        $menunggu = Status::where('konteks', 'jadwal_service_mesin')->where('kode', 'menunggu_persetujuan')->first();

        $jadwal = JadwalServiceMesin::create([
            'mesin_id' => $request->mesin_id,
            'outlet_id' => $request->outlet_id,
            'user_id' => $request->user()->id,
            'tanggal_pengajuan' => now(),
            'deskripsi' => $request->deskripsi,
            'status_id' => $menunggu?->id,
        ]);

        return response()->json([
            'message' => 'Pengajuan service berhasil.',
            'data' => $this->format($jadwal->fresh()->load(['mesin', 'outlet', 'user', 'status'])),
        ], 201);
    }

    public function show(JadwalServiceMesin $jadwal): JsonResponse
    {
        $jadwal->load(['mesin', 'outlet', 'user', 'status']);
        return response()->json(['data' => $this->format($jadwal)]);
    }

    /**
     * Franchisor validasi (setuju / tolak)
     */
    public function validate(Request $request, JadwalServiceMesin $jadwal): JsonResponse
    {
        $request->validate([
            'status' => 'required|in:disetujui,ditolak',
        ]);

        $newStatus = Status::where('konteks', 'jadwal_service_mesin')->where('kode', $request->status)->first();
        $jadwal->update(['status_id' => $newStatus?->id]);

        return response()->json(['message' => 'Validasi service berhasil.']);
    }

    /**
     * Manajer Outlet tandai selesai
     */
    public function complete(JadwalServiceMesin $jadwal): JsonResponse
    {
        $selesai = Status::where('konteks', 'jadwal_service_mesin')->where('kode', 'selesai')->first();
        $jadwal->update(['status_id' => $selesai?->id, 'tanggal_service' => now()]);

        return response()->json(['message' => 'Service selesai.']);
    }

    private function format($j): array
    {
        return [
            'id' => $j->id,
            'mesin' => $j->mesin ? [
                'id' => $j->mesin->id,
                'nama' => $j->mesin->nama,
                'kode_mesin' => $j->mesin->kode_mesin,
            ] : null,
            'outlet' => $j->outlet ? ['id' => $j->outlet->id, 'nama' => $j->outlet->nama] : null,
            'user' => $j->user ? ['id' => $j->user->id, 'nama' => $j->user->nama] : null,
            'tanggal_pengajuan' => $j->tanggal_pengajuan?->format('Y-m-d'),
            'tanggal_service' => $j->tanggal_service?->format('Y-m-d'),
            'deskripsi' => $j->deskripsi,
            'menunggu_mesin_bebas' => $j->menunggu_mesin_bebas,
            'status' => $j->status ? ['id' => $j->status->id, 'kode' => $j->status->kode, 'label' => $j->status->label] : null,
            'created_at' => $j->created_at?->toDateTimeString(),
        ];
    }
}
