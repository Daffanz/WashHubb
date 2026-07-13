<?php
namespace App\Http\Controllers\Api\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Franchise;
use App\Models\StokOutletBahanBaku;
use App\Models\StokOutletMesin;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StokOutletController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $type = $request->query('type', 'bahan_baku');
        $user = $request->user();
        $roleKode = $user->role?->kode;

        // Tentukan outlet_id berdasarkan role
        $outletId = $request->query('outlet_id');

        if (in_array($roleKode, ['manager_outlet', 'franchise'])) {
            // Manager outlet & franchise hanya lihat stok outletnya sendiri
            if ($roleKode === 'franchise') {
                $franchise = Franchise::where('user_id', $user->id)->first();
                $outletIds = $franchise ? $franchise->outlets()->pluck('outlets.id')->toArray() : [];
            } else {
                $outletIds = $user->outlets()->pluck('outlets.id')->toArray();
            }

            if ($type === 'mesin') {
                $query = StokOutletMesin::with(['mesin', 'outlet'])->whereIn('outlet_id', $outletIds);
                $stocks = $query->paginate(15);

                return response()->json([
                    'data' => $stocks->map(fn ($s) => [
                        'id' => $s->id,
                        'outlet' => ['id' => $s->outlet->id, 'nama' => $s->outlet->nama],
                        'mesin' => ['id' => $s->mesin->id, 'nama' => $s->mesin->nama],
                        'stok_saat_ini' => (int) $s->stok_saat_ini,
                        'stok_minimum' => (int) $s->stok_minimum,
                        'stok_masuk' => (int) $s->stok_masuk,
                        'stok_keluar' => (int) $s->stok_keluar,
                        'updated_at' => $s->updated_at?->toDateTimeString(),
                    ]),
                    'meta' => [
                        'current_page' => $stocks->currentPage(),
                        'last_page' => $stocks->lastPage(),
                        'per_page' => $stocks->perPage(),
                        'total' => $stocks->total(),
                    ],
                ]);
            }

            // Bahan baku
            $query = StokOutletBahanBaku::with(['bahanBaku.kategori', 'outlet'])->whereIn('outlet_id', $outletIds);
            $stocks = $query->paginate(15);

            return response()->json([
                'data' => $stocks->map(fn ($s) => [
                    'id' => $s->id,
                    'outlet' => ['id' => $s->outlet->id, 'nama' => $s->outlet->nama],
                    'bahan_baku' => [
                        'id' => $s->bahanBaku->id,
                        'nama' => $s->bahanBaku->nama,
                        'satuan' => $s->bahanBaku->satuan,
                        'kategori' => $s->bahanBaku->kategori?->nama,
                    ],
                    'stok_saat_ini' => (float) $s->stok_saat_ini,
                    'stok_minimum' => (float) $s->stok_minimum,
                    'stok_masuk' => (float) $s->stok_masuk,
                    'stok_keluar' => (float) $s->stok_keluar,
                    'updated_at' => $s->updated_at?->toDateTimeString(),
                ]),
                'meta' => [
                    'current_page' => $stocks->currentPage(),
                    'last_page' => $stocks->lastPage(),
                    'per_page' => $stocks->perPage(),
                    'total' => $stocks->total(),
                ],
            ]);
        }

        // Admin, franchisor, procurement — bisa filter per outlet atau lihat semua
        if ($type === 'mesin') {
            $query = StokOutletMesin::with(['mesin', 'outlet']);
            if ($outletId) {
                $query->where('outlet_id', $outletId);
            }
            $stocks = $query->paginate(15);

            return response()->json([
                'data' => $stocks->map(fn ($s) => [
                    'id' => $s->id,
                    'outlet' => ['id' => $s->outlet->id, 'nama' => $s->outlet->nama],
                    'mesin' => ['id' => $s->mesin->id, 'nama' => $s->mesin->nama],
                    'stok_saat_ini' => (int) $s->stok_saat_ini,
                    'stok_minimum' => (int) $s->stok_minimum,
                    'stok_masuk' => (int) $s->stok_masuk,
                    'stok_keluar' => (int) $s->stok_keluar,
                    'updated_at' => $s->updated_at?->toDateTimeString(),
                ]),
                'meta' => [
                    'current_page' => $stocks->currentPage(),
                    'last_page' => $stocks->lastPage(),
                    'per_page' => $stocks->perPage(),
                    'total' => $stocks->total(),
                ],
            ]);
        }

        // Default: bahan_baku
        $query = StokOutletBahanBaku::with(['bahanBaku.kategori', 'outlet']);
        if ($outletId) {
            $query->where('outlet_id', $outletId);
        }
        $stocks = $query->paginate(15);

        return response()->json([
            'data' => $stocks->map(fn ($s) => [
                'id' => $s->id,
                'outlet' => ['id' => $s->outlet->id, 'nama' => $s->outlet->nama],
                'bahan_baku' => [
                    'id' => $s->bahanBaku->id,
                    'nama' => $s->bahanBaku->nama,
                    'satuan' => $s->bahanBaku->satuan,
                    'kategori' => $s->bahanBaku->kategori?->nama,
                ],
                'stok_saat_ini' => (float) $s->stok_saat_ini,
                'stok_minimum' => (float) $s->stok_minimum,
                'stok_masuk' => (float) $s->stok_masuk,
                'stok_keluar' => (float) $s->stok_keluar,
                'updated_at' => $s->updated_at?->toDateTimeString(),
            ]),
            'meta' => [
                'current_page' => $stocks->currentPage(),
                'last_page' => $stocks->lastPage(),
                'per_page' => $stocks->perPage(),
                'total' => $stocks->total(),
            ],
        ]);
    }
}
