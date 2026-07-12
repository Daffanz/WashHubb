<?php
namespace App\Http\Controllers\Api\Inventory;

use App\Http\Controllers\Controller;
use App\Models\StokPusatBahanBaku;
use App\Models\StokPusatMesin;
use App\Models\MutasiStokPusatBahanBaku;
use App\Models\MutasiStokPusatMesin;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $type = $request->query('type', 'bahan');

        if ($type === 'mesin') {
            $stocks = StokPusatMesin::with('mesin')->paginate(15);
            return response()->json([
                'data' => $stocks->map(fn ($s) => [
                    'id' => $s->id, 'mesin' => ['id' => $s->mesin->id, 'nama' => $s->mesin->nama, 'kode_mesin' => $s->mesin->kode_mesin],
                    'stok_masuk' => $s->stok_masuk, 'stok_keluar' => $s->stok_keluar, 'stok_saat_ini' => $s->stok_saat_ini,
                    'updated_at' => $s->updated_at?->toDateTimeString(),
                ]),
                'meta' => ['current_page' => $stocks->currentPage(), 'last_page' => $stocks->lastPage(), 'per_page' => $stocks->perPage(), 'total' => $stocks->total()],
            ]);
        }

        $stocks = StokPusatBahanBaku::with('bahanBaku.kategori')->paginate(15);
        return response()->json([
            'data' => $stocks->map(fn ($s) => [
                'id' => $s->id, 'bahan_baku' => ['id' => $s->bahanBaku->id, 'nama' => $s->bahanBaku->nama, 'satuan' => $s->bahanBaku->satuan, 'kategori' => $s->bahanBaku->kategori?->nama],
                'stok_masuk' => $s->stok_masuk, 'stok_keluar' => $s->stok_keluar, 'stok_saat_ini' => $s->stok_saat_ini,
                'updated_at' => $s->updated_at?->toDateTimeString(),
            ]),
            'meta' => ['current_page' => $stocks->currentPage(), 'last_page' => $stocks->lastPage(), 'per_page' => $stocks->perPage(), 'total' => $stocks->total()],
        ]);
    }

    public function show(string $type, int $id): JsonResponse
    {
        if ($type === 'mesin') {
            $s = StokPusatMesin::with('mesin')->findOrFail($id);
            return response()->json(['data' => ['id' => $s->id, 'mesin' => ['id' => $s->mesin->id, 'nama' => $s->mesin->nama, 'kode_mesin' => $s->mesin->kode_mesin], 'stok_masuk' => $s->stok_masuk, 'stok_keluar' => $s->stok_keluar, 'stok_saat_ini' => $s->stok_saat_ini]]);
        }
        $s = StokPusatBahanBaku::with('bahanBaku.kategori')->findOrFail($id);
        return response()->json(['data' => ['id' => $s->id, 'bahan_baku' => ['id' => $s->bahanBaku->id, 'nama' => $s->bahanBaku->nama, 'satuan' => $s->bahanBaku->satuan], 'stok_masuk' => $s->stok_masuk, 'stok_keluar' => $s->stok_keluar, 'stok_saat_ini' => $s->stok_saat_ini]]);
    }
}
