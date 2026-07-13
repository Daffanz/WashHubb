<?php
namespace App\Http\Controllers\Api\Operasional;

use App\Http\Controllers\Controller;
use App\Models\JadwalServiceMesin;
use App\Models\DetailMesin;
use App\Models\Status;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class JadwalServiceController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $query = JadwalServiceMesin::with(['detailMesin.mesin', 'outlet', 'user', 'status']);

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
            'detail_mesin_id' => 'required|exists:detail_mesins,id',
            'outlet_id' => 'required|exists:outlets,id',
            'deskripsi' => 'required|string',
        ]);

        $jadwal = DB::transaction(function () use ($request) {
            $mesin = DetailMesin::findOrFail($request->detail_mesin_id);
            $mesinStatus = $mesin->status?->kode;

            // Auto-tolak jika mesin maintenance/nonaktif
            if (in_array($mesinStatus, ['maintenance', 'nonaktif'])) {
                $ditolak = Status::where('konteks', 'jadwal_service_mesin')->where('kode', 'ditolak')->first();
                return JadwalServiceMesin::create([
                    'detail_mesin_id' => $request->detail_mesin_id,
                    'outlet_id' => $request->outlet_id,
                    'user_id' => $request->user()->id,
                    'tanggal_pengajuan' => now(),
                    'deskripsi' => $request->deskripsi,
                    'status_id' => $ditolak?->id,
                ]);
            }

            $menunggu = Status::where('konteks', 'jadwal_service_mesin')->where('kode', 'menunggu_persetujuan')->first();
            return JadwalServiceMesin::create([
                'detail_mesin_id' => $request->detail_mesin_id,
                'outlet_id' => $request->outlet_id,
                'user_id' => $request->user()->id,
                'tanggal_pengajuan' => now(),
                'deskripsi' => $request->deskripsi,
                'menunggu_mesin_bebas' => $mesinStatus === 'digunakan',
                'status_id' => $menunggu?->id,
            ]);
        });

        return response()->json([
            'message' => $jadwal->status?->kode === 'ditolak'
                ? 'Mesin sedang maintenance/nonaktif. Pengajuan otomatis ditolak.'
                : 'Pengajuan service berhasil.',
            'data' => $this->format($jadwal->fresh()->load(['detailMesin.mesin', 'outlet', 'user', 'status'])),
        ], 201);
    }

    public function show(JadwalServiceMesin $jadwal): JsonResponse
    {
        $jadwal->load(['detailMesin.mesin', 'outlet', 'user', 'status']);
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

        DB::transaction(function () use ($request, $jadwal) {
            $newStatus = Status::where('konteks', 'jadwal_service_mesin')->where('kode', $request->status)->first();
            $jadwal->update(['status_id' => $newStatus?->id]);

            if ($request->status === 'disetujui') {
                $mesin = DetailMesin::lockForUpdate()->findOrFail($jadwal->detail_mesin_id);
                $mesinStatus = $mesin->status?->kode;

                if ($mesinStatus === 'aktif') {
                    // Langsung ke maintenance
                    $maintenance = Status::where('konteks', 'detail_mesin')->where('kode', 'maintenance')->first();
                    $mesin->update(['status_id' => $maintenance?->id]);
                    $jadwal->update(['menunggu_mesin_bebas' => false]);
                }
                // Jika digunakan → menunggu_mesin_bebas tetap true, mesin diubah saat order selesai
            }
        });

        return response()->json(['message' => 'Validasi service berhasil.']);
    }

    /**
     * Manajer Outlet tandai selesai
     */
    public function complete(JadwalServiceMesin $jadwal): JsonResponse
    {
        DB::transaction(function () use ($jadwal) {
            $selesai = Status::where('konteks', 'jadwal_service_mesin')->where('kode', 'selesai')->first();
            $jadwal->update(['status_id' => $selesai?->id, 'tanggal_service' => now()]);

            // Mesin kembali aktif
            $mesin = DetailMesin::lockForUpdate()->findOrFail($jadwal->detail_mesin_id);
            $aktif = Status::where('konteks', 'detail_mesin')->where('kode', 'aktif')->first();
            $mesin->update(['status_id' => $aktif?->id]);
        });

        return response()->json(['message' => 'Service selesai. Mesin kembali aktif.']);
    }

    private function format($j): array
    {
        return [
            'id' => $j->id,
            'detail_mesin' => $j->detailMesin ? [
                'id' => $j->detailMesin->id,
                'mesin' => $j->detailMesin->mesin?->nama,
                'nomor_seri' => $j->detailMesin->nomor_seri,
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
