<?php
namespace App\Http\Controllers\Api\Inventory;

use App\Http\Controllers\Controller;
use App\Models\MutasiStokPusatBahanBaku;
use App\Models\MutasiStokPusatMesin;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MutasiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $type = $request->query('type', 'bahan');

        if ($type === 'mesin') {
            $mutasis = MutasiStokPusatMesin::with('stokPusat.mesin')->orderByDesc('tanggal')->paginate(15);
        } else {
            $mutasis = MutasiStokPusatBahanBaku::with('stokPusat.bahanBaku')->orderByDesc('tanggal')->paginate(15);
        }

        return response()->json([
            'data' => $mutasis->map(fn ($m) => [
                'id' => $m->id, 'jenis_mutasi' => $m->jenis_mutasi, 'jumlah' => $m->jumlah,
                'tanggal' => $m->tanggal?->toDateTimeString(), 'created_at' => $m->created_at?->toDateTimeString(),
            ]),
            'meta' => ['current_page' => $mutasis->currentPage(), 'last_page' => $mutasis->lastPage(), 'per_page' => $mutasis->perPage(), 'total' => $mutasis->total()],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'stok_type' => 'required|in:bahan,mesin',
            'stok_id' => 'required|integer',
            'jenis_mutasi' => 'required|in:masuk,keluar,penyesuaian',
            'jumlah' => 'required|numeric|min:0.0001',
            'keterangan' => 'nullable|string',
        ]);

        DB::transaction(function () use ($request) {
            if ($request->stok_type === 'mesin') {
                $stok = \App\Models\StokPusatMesin::lockForUpdate()->findOrFail($request->stok_id);
                $current = $stok->stok_saat_ini;
                if ($request->jenis_mutasi === 'keluar' && $current < $request->jumlah) {
                    throw new \InvalidArgumentException('Stok tidak cukup.');
                }
                $new = match ($request->jenis_mutasi) {
                    'masuk' => $current + (int) $request->jumlah,
                    'keluar' => $current - (int) $request->jumlah,
                    default => (int) $request->jumlah,
                };
                $stok->update(['stok_saat_ini' => $new, 'stok_masuk' => $request->jenis_mutasi === 'masuk' ? $stok->stok_masuk + (int) $request->jumlah : $stok->stok_masuk, 'stok_keluar' => $request->jenis_mutasi === 'keluar' ? $stok->stok_keluar + (int) $request->jumlah : $stok->stok_keluar]);
                MutasiStokPusatMesin::create(['stok_pusat_mesin_id' => $stok->id, 'jenis_mutasi' => $request->jenis_mutasi, 'jumlah' => $request->jumlah, 'tanggal' => now()]);
            } else {
                $stok = \App\Models\StokPusatBahanBaku::lockForUpdate()->findOrFail($request->stok_id);
                $current = (float) $stok->stok_saat_ini;
                if ($request->jenis_mutasi === 'keluar' && $current < $request->jumlah) {
                    throw new \InvalidArgumentException('Stok tidak cukup.');
                }
                $new = match ($request->jenis_mutasi) {
                    'masuk' => $current + (float) $request->jumlah,
                    'keluar' => $current - (float) $request->jumlah,
                    default => (float) $request->jumlah,
                };
                $stok->update(['stok_saat_ini' => $new, 'stok_masuk' => $request->jenis_mutasi === 'masuk' ? (float) $stok->stok_masuk + (float) $request->jumlah : $stok->stok_masuk, 'stok_keluar' => $request->jenis_mutasi === 'keluar' ? (float) $stok->stok_keluar + (float) $request->jumlah : $stok->stok_keluar]);
                MutasiStokPusatBahanBaku::create(['stok_pusat_bahan_baku_id' => $stok->id, 'jenis_mutasi' => $request->jenis_mutasi, 'jumlah' => $request->jumlah, 'tanggal' => now()]);
            }
        });

        return response()->json(['message' => 'Mutasi stok berhasil dicatat.'], 201);
    }
}
