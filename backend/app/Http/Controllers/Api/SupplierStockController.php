<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\StokSupplierBahanBaku;
use App\Models\StokSupplierMesin;
use App\Models\MutasiStokSupplierBahanBaku;
use App\Models\MutasiStokSupplierMesin;
use App\Models\Status;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SupplierStockController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $supplier = $request->user()->suppliers()->first();
        if (! $supplier) return response()->json(['data' => []]);

        if ($supplier->jenis_supplier === 'bahan_baku') {
            $stocks = StokSupplierBahanBaku::with('bahanBaku.kategori', 'status')->where('supplier_id', $supplier->id)->paginate(15);
        } else {
            $stocks = StokSupplierMesin::with('mesin', 'status')->where('supplier_id', $supplier->id)->paginate(15);
        }

        return response()->json([
            'data' => $stocks->map(fn ($s) => $this->formatStok($s, $supplier->jenis_supplier)),
            'meta' => ['current_page' => $stocks->currentPage(), 'last_page' => $stocks->lastPage(), 'per_page' => $stocks->perPage(), 'total' => $stocks->total()],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $supplier = $request->user()->suppliers()->first();
        if (! $supplier) return response()->json(['message' => 'Anda bukan supplier.'], 403);

        $request->validate([
            'bahan_baku_id' => 'required_if:jenis,bahan_baku|exists:bahan_bakus,id',
            'mesin_id' => 'required_if:jenis,mesin|exists:mesins,id',
            'jenis' => 'required|in:bahan_baku,mesin',
            'jumlah' => 'required|numeric|min:1',
        ]);

        // Guard: jenis harus sesuai jenis_supplier
        if ($request->jenis !== $supplier->jenis_supplier) {
            return response()->json(['message' => 'Jenis item tidak sesuai dengan jenis supplier Anda.'], 422);
        }

        $aktifStatus = Status::where('konteks', 'stok_supplier')->where('kode', 'aktif')->first();

        DB::transaction(function () use ($request, $supplier, $aktifStatus) {
            if ($request->jenis === 'bahan_baku') {
                $stok = StokSupplierBahanBaku::firstOrCreate(
                    ['supplier_id' => $supplier->id, 'bahan_baku_id' => $request->bahan_baku_id],
                    ['stok_saat_ini' => 0, 'status_id' => $aktifStatus?->id]
                );
                $stok->increment('stok_saat_ini', $request->jumlah);
                MutasiStokSupplierBahanBaku::create([
                    'stok_supplier_bahan_baku_id' => $stok->id,
                    'jenis_mutasi' => 'masuk',
                    'jumlah' => $request->jumlah,
                    'tanggal' => now(),
                ]);
            } else {
                $stok = StokSupplierMesin::firstOrCreate(
                    ['supplier_id' => $supplier->id, 'mesin_id' => $request->mesin_id],
                    ['stok_saat_ini' => 0, 'status_id' => $aktifStatus?->id]
                );
                $stok->increment('stok_saat_ini', (int) $request->jumlah);
                MutasiStokSupplierMesin::create([
                    'stok_supplier_mesin_id' => $stok->id,
                    'jenis_mutasi' => 'masuk',
                    'jumlah' => (int) $request->jumlah,
                    'tanggal' => now(),
                ]);
            }
        });

        return response()->json(['message' => 'Stok berhasil ditambahkan.'], 201);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $supplier = $request->user()->suppliers()->first();
        if (! $supplier) return response()->json(['message' => 'Anda bukan supplier.'], 403);

        if ($supplier->jenis_supplier === 'bahan_baku') {
            $stok = StokSupplierBahanBaku::where('supplier_id', $supplier->id)->where('id', $id)->first();
            if (! $stok) return response()->json(['message' => 'Stok tidak ditemukan.'], 404);
            // PRD Rule 4: Hapus hanya jika belum pernah didistribusikan
            $pernahDikirim = MutasiStokSupplierBahanBaku::where('stok_supplier_bahan_baku_id', $stok->id)->where('jenis_mutasi', 'keluar')->exists();
            if ($pernahDikirim) {
                return response()->json(['message' => 'Stok sudah pernah didistribusikan, tidak bisa dihapus.'], 422);
            }
            $stok->delete();
        } else {
            $stok = StokSupplierMesin::where('supplier_id', $supplier->id)->where('id', $id)->first();
            if (! $stok) return response()->json(['message' => 'Stok tidak ditemukan.'], 404);
            $pernahDikirim = MutasiStokSupplierMesin::where('stok_supplier_mesin_id', $stok->id)->where('jenis_mutasi', 'keluar')->exists();
            if ($pernahDikirim) {
                return response()->json(['message' => 'Stok sudah pernah didistribusikan, tidak bisa dihapus.'], 422);
            }
            $stok->delete();
        }

        return response()->json(['message' => 'Stok berhasil dihapus.']);
    }

    private function formatStok($s, string $jenis): array
    {
        $base = ['id' => $s->id, 'stok_saat_ini' => $s->stok_saat_ini, 'status' => $s->status ? ['kode' => $s->status->kode, 'label' => $s->status->label] : null];
        if ($jenis === 'bahan_baku') {
            $base['bahan_baku'] = $s->bahanBaku ? ['id' => $s->bahanBaku->id, 'nama' => $s->bahanBaku->nama, 'satuan' => $s->bahanBaku->satuan, 'kategori' => $s->bahanBaku->kategori?->nama] : null;
        } else {
            $base['mesin'] = $s->mesin ? ['id' => $s->mesin->id, 'nama' => $s->mesin->nama, 'kode_mesin' => $s->mesin->kode_mesin] : null;
        }
        return $base;
    }
}
