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
            'jenis_supplier' => $supplier->jenis_supplier,
        ]);
    }

    /**
     * Detail riwayat stok supplier per item
     * Menampilkan: masuk manual, keluar distribusi, diterima, cacat, retur
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $supplier = $request->user()->suppliers()->first();
        if (! $supplier) return response()->json(['message' => 'Anda bukan supplier.'], 403);

        $riwayat = [];
        $stokMasuk = 0;
        $stokKeluar = 0;

        if ($supplier->jenis_supplier === 'bahan_baku') {
            $stok = StokSupplierBahanBaku::with('bahanBaku.kategori')->where('supplier_id', $supplier->id)->where('id', $id)->firstOrFail();
            $itemId = $stok->bahan_baku_id;

            // 1. Mutasi masuk manual
            $mutasiMasuk = MutasiStokSupplierBahanBaku::where('stok_supplier_bahan_baku_id', $stok->id)
                ->where('jenis_mutasi', 'masuk')->get();
            foreach ($mutasiMasuk as $m) {
                $stokMasuk += (float) $m->jumlah;
                $riwayat[] = [
                    'tanggal' => $m->created_at,
                    'jenis' => 'masuk',
                    'keterangan' => 'Tambah stok manual',
                    'jumlah_kirim' => 0,
                    'diterima' => 0,
                    'cacat' => 0,
                    'retur' => 0,
                    'tujuan' => '-',
                ];
            }

            // 2. Distribusi keluar
            $distribusis = \App\Models\DistribusiBarang::whereHas('po', fn ($q) => $q->where('supplier_id', $supplier->id))
                ->whereHas('detailBahanBakus', fn ($q) => $q->where('po_item_id', function ($sub) use ($itemId) {
                    $sub->select('id')->from('purchase_order_item_bahan_bakus')->where('bahan_baku_id', $itemId)->limit(1);
                }))
                ->with(['po', 'status', 'detailBahanBakus' => fn ($q) => $q->whereHas('poItem', fn ($sq) => $sq->where('bahan_baku_id', $itemId))])
                ->get();

            foreach ($distribusis as $dist) {
                $jumlahKirim = $dist->detailBahanBakus->sum('jumlah_kirim');
                $stokKeluar += (float) $jumlahKirim;

                // Cek penerimaan
                $penerimaans = \App\Models\PenerimaanBarang::where('distribusi_barang_id', $dist->id)
                    ->with(['detailBahanBakus' => fn ($q) => $q->whereHas('distribusiDetail', fn ($sq) => $sq->whereHas('poItem', fn ($ssq) => $ssq->where('bahan_baku_id', $itemId)))])
                    ->get();

                $diterima = 0;
                $cacat = 0;
                foreach ($penerimaans as $p) {
                    foreach ($p->detailBahanBakus as $d) {
                        if ($d->kondisi === 'baik') $diterima += (float) $d->qty_diterima;
                        else $cacat += (float) $d->qty_diterima;
                    }
                }

                // Cek retur
                $returQty = 0;
                foreach ($penerimaans as $p) {
                    $returs = \App\Models\ReturBarang::where('penerimaan_barang_id', $p->id)
                        ->with(['detailBahanBakus' => fn ($q) => $q->whereHas('penerimaanDetail', fn ($sq) => $sq->whereHas('distribusiDetail', fn ($ssq) => $ssq->whereHas('poItem', fn ($sssq) => $sssq->where('bahan_baku_id', $itemId))))])
                        ->get();
                    foreach ($returs as $r) {
                        $returQty += $r->detailBahanBakus->sum('qty_retur');
                    }
                }

                $riwayat[] = [
                    'tanggal' => $dist->created_at,
                    'jenis' => 'keluar',
                    'keterangan' => 'Distribusi ' . $dist->nomor_distribusi,
                    'jumlah_kirim' => (float) $jumlahKirim,
                    'diterima' => (float) $diterima,
                    'cacat' => (float) $cacat,
                    'retur' => (float) $returQty,
                    'tujuan' => 'Tim Pengadaan',
                ];
            }

            return response()->json([
                'data' => [
                    'id' => $stok->id,
                    'item' => ['id' => $stok->bahanBaku->id, 'nama' => $stok->bahanBaku->nama, 'satuan' => $stok->bahanBaku->satuan, 'kategori' => $stok->bahanBaku->kategori?->nama],
                    'stok_masuk' => $stokMasuk,
                    'stok_keluar' => $stokKeluar,
                    'stok_saat_ini' => (float) $stok->stok_saat_ini,
                    'riwayat' => collect($riwayat)->sortBy('tanggal')->values(),
                ],
            ]);
        }

        // Mesin (similar logic)
        $stok = StokSupplierMesin::with('mesin')->where('supplier_id', $supplier->id)->where('id', $id)->firstOrFail();
        $itemId = $stok->mesin_id;

        $mutasiMasuk = MutasiStokSupplierMesin::where('stok_supplier_mesin_id', $stok->id)
            ->where('jenis_mutasi', 'masuk')->get();
        foreach ($mutasiMasuk as $m) {
            $stokMasuk += (int) $m->jumlah;
            $riwayat[] = [
                'tanggal' => $m->created_at,
                'jenis' => 'masuk',
                'keterangan' => 'Tambah stok manual',
                'jumlah_kirim' => 0,
                'diterima' => 0,
                'cacat' => 0,
                'retur' => 0,
                'tujuan' => '-',
            ];
        }

        $distribusis = \App\Models\DistribusiBarang::whereHas('po', fn ($q) => $q->where('supplier_id', $supplier->id))
            ->whereHas('detailMesins', fn ($q) => $q->where('po_item_id', function ($sub) use ($itemId) {
                $sub->select('id')->from('purchase_order_item_mesins')->where('mesin_id', $itemId)->limit(1);
            }))
            ->with(['po', 'status', 'detailMesins' => fn ($q) => $q->whereHas('poItem', fn ($sq) => $sq->where('mesin_id', $itemId))])
            ->get();

        foreach ($distribusis as $dist) {
            $jumlahKirim = $dist->detailMesins->sum('jumlah_kirim');
            $stokKeluar += (int) $jumlahKirim;

            $penerimaans = \App\Models\PenerimaanBarang::where('distribusi_barang_id', $dist->id)
                ->with(['detailMesins' => fn ($q) => $q->whereHas('distribusiDetail', fn ($sq) => $sq->whereHas('poItem', fn ($ssq) => $ssq->where('mesin_id', $itemId)))])
                ->get();

            $diterima = 0;
            $cacat = 0;
            foreach ($penerimaans as $p) {
                foreach ($p->detailMesins as $d) {
                    if ($d->kondisi === 'baik') $diterima += (int) $d->qty_diterima;
                    else $cacat += (int) $d->qty_diterima;
                }
            }

            $returQty = 0;
            foreach ($penerimaans as $p) {
                $returs = \App\Models\ReturBarang::where('penerimaan_barang_id', $p->id)
                    ->with(['detailMesins' => fn ($q) => $q->whereHas('penerimaanDetail', fn ($sq) => $sq->whereHas('distribusiDetail', fn ($ssq) => $ssq->whereHas('poItem', fn ($sssq) => $sssq->where('mesin_id', $itemId))))])
                    ->get();
                foreach ($returs as $r) {
                    $returQty += $r->detailMesins->sum('qty_retur');
                }
            }

            $riwayat[] = [
                'tanggal' => $dist->created_at,
                'jenis' => 'keluar',
                'keterangan' => 'Distribusi ' . $dist->nomor_distribusi,
                'jumlah_kirim' => (int) $jumlahKirim,
                'diterima' => (int) $diterima,
                'cacat' => (int) $cacat,
                'retur' => (int) $returQty,
                'tujuan' => 'Tim Pengadaan',
            ];
        }

        return response()->json([
            'data' => [
                'id' => $stok->id,
                'item' => ['id' => $stok->mesin->id, 'nama' => $stok->mesin->nama, 'kode_mesin' => $stok->mesin->kode_mesin],
                'stok_masuk' => $stokMasuk,
                'stok_keluar' => $stokKeluar,
                'stok_saat_ini' => (int) $stok->stok_saat_ini,
                'riwayat' => collect($riwayat)->sortBy('tanggal')->values(),
            ],
        ]);
    }

    /**
     * UC-39: Supplier tambah stok (multi-item)
     * Guard: jenis item harus sesuai jenis_supplier
     */
    public function store(Request $request): JsonResponse
    {
        $supplier = $request->user()->suppliers()->first();
        if (! $supplier) return response()->json(['message' => 'Anda bukan supplier.'], 403);

        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|integer',
            'items.*.jumlah' => 'required|numeric|min:1',
        ]);

        $aktifStatus = Status::where('konteks', 'stok_supplier')->where('kode', 'aktif')->first();

        DB::transaction(function () use ($request, $supplier, $aktifStatus) {
            foreach ($request->items as $item) {
                if ($supplier->jenis_supplier === 'bahan_baku') {
                    $stok = StokSupplierBahanBaku::firstOrCreate(
                        ['supplier_id' => $supplier->id, 'bahan_baku_id' => $item['item_id']],
                        ['stok_saat_ini' => 0, 'status_id' => $aktifStatus?->id]
                    );
                    $stok->increment('stok_saat_ini', $item['jumlah']);
                    MutasiStokSupplierBahanBaku::create([
                        'stok_supplier_bahan_baku_id' => $stok->id,
                        'jenis_mutasi' => 'masuk',
                        'jumlah' => $item['jumlah'],
                        'tanggal' => now(),
                    ]);
                } else {
                    $stok = StokSupplierMesin::firstOrCreate(
                        ['supplier_id' => $supplier->id, 'mesin_id' => $item['item_id']],
                        ['stok_saat_ini' => 0, 'status_id' => $aktifStatus?->id]
                    );
                    $stok->increment('stok_saat_ini', (int) $item['jumlah']);
                    MutasiStokSupplierMesin::create([
                        'stok_supplier_mesin_id' => $stok->id,
                        'jenis_mutasi' => 'masuk',
                        'jumlah' => (int) $item['jumlah'],
                        'tanggal' => now(),
                    ]);
                }
            }
        });

        return response()->json(['message' => 'Stok berhasil ditambahkan.'], 201);
    }

    /**
     * Supplier tambah stok ke item yang sudah ada
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $supplier = $request->user()->suppliers()->first();
        if (! $supplier) return response()->json(['message' => 'Anda bukan supplier.'], 403);

        $request->validate([
            'jumlah' => 'required|numeric|min:1',
        ]);

        if ($supplier->jenis_supplier === 'bahan_baku') {
            $stok = StokSupplierBahanBaku::where('supplier_id', $supplier->id)->where('id', $id)->first();
            if (! $stok) return response()->json(['message' => 'Stok tidak ditemukan.'], 404);

            DB::transaction(function () use ($stok, $request) {
                $stok->increment('stok_saat_ini', $request->jumlah);
                MutasiStokSupplierBahanBaku::create([
                    'stok_supplier_bahan_baku_id' => $stok->id,
                    'jenis_mutasi' => 'masuk',
                    'jumlah' => $request->jumlah,
                    'tanggal' => now(),
                ]);
            });

            $stok->refresh();
            return response()->json([
                'message' => 'Stok berhasil ditambahkan.',
                'data' => $this->formatStok($stok->load('bahanBaku.kategori', 'status'), 'bahan_baku'),
            ]);
        }

        $stok = StokSupplierMesin::where('supplier_id', $supplier->id)->where('id', $id)->first();
        if (! $stok) return response()->json(['message' => 'Stok tidak ditemukan.'], 404);

        DB::transaction(function () use ($stok, $request) {
            $stok->increment('stok_saat_ini', (int) $request->jumlah);
            MutasiStokSupplierMesin::create([
                'stok_supplier_mesin_id' => $stok->id,
                'jenis_mutasi' => 'masuk',
                'jumlah' => (int) $request->jumlah,
                'tanggal' => now(),
            ]);
        });

        $stok->refresh();
        return response()->json([
            'message' => 'Stok berhasil ditambahkan.',
            'data' => $this->formatStok($stok->load('mesin', 'status'), 'mesin'),
        ]);
    }

    /**
     * UC-40: Supplier hapus stok (hanya jika belum pernah didistribusikan)
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $supplier = $request->user()->suppliers()->first();
        if (! $supplier) return response()->json(['message' => 'Anda bukan supplier.'], 403);

        if ($supplier->jenis_supplier === 'bahan_baku') {
            $stok = StokSupplierBahanBaku::where('supplier_id', $supplier->id)->where('id', $id)->first();
            if (! $stok) return response()->json(['message' => 'Stok tidak ditemukan.'], 404);
            $pernahDikirim = MutasiStokSupplierBahanBaku::where('stok_supplier_bahan_baku_id', $stok->id)->where('jenis_mutasi', 'keluar')->exists();
            if ($pernahDikirim) return response()->json(['message' => 'Stok sudah pernah didistribusikan, tidak bisa dihapus.'], 422);
            $stok->delete();
        } else {
            $stok = StokSupplierMesin::where('supplier_id', $supplier->id)->where('id', $id)->first();
            if (! $stok) return response()->json(['message' => 'Stok tidak ditemukan.'], 404);
            $pernahDikirim = MutasiStokSupplierMesin::where('stok_supplier_mesin_id', $stok->id)->where('jenis_mutasi', 'keluar')->exists();
            if ($pernahDikirim) return response()->json(['message' => 'Stok sudah pernah didistribusikan, tidak bisa dihapus.'], 422);
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
