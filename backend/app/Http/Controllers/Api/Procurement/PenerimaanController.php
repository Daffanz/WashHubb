<?php

namespace App\Http\Controllers\Api\Procurement;

use App\Http\Controllers\Controller;
use App\Models\PenerimaanBarang;
use App\Models\PenerimaanDetailBahanBaku;
use App\Models\PenerimaanDetailMesin;
use App\Models\ReturBarang;
use App\Models\ReturDetailBahanBaku;
use App\Models\ReturDetailMesin;
use App\Models\Status;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PenerimaanController extends Controller
{
    public function __construct(
        private readonly \App\Services\StockService $stockService,
    ) {}

    public function index(): JsonResponse
    {
        $penerimaans = PenerimaanBarang::with(['distribusiBarang.po', 'detailBahanBakus.distribusiDetail.poItem.bahanBaku', 'detailMesins.distribusiDetail.poItem.mesin'])->paginate(15);
        return response()->json([
            'data' => $penerimaans->map(fn ($p) => $this->formatPenerimaan($p)),
            'meta' => ['current_page' => $penerimaans->currentPage(), 'last_page' => $penerimaans->lastPage(), 'per_page' => $penerimaans->perPage(), 'total' => $penerimaans->total()],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'distribusi_barang_id' => 'required|exists:distribusi_barangs,id',
            'tanggal_terima' => 'required|date',
            'subtotal' => 'required|numeric|min:0',
            'diskon' => 'required|numeric|min:0',
            'ppn' => 'required|numeric|min:0',
            'total_bayar' => 'required|numeric|min:0',
            'items_bahan_baku' => 'sometimes|array',
            'items_bahan_baku.*.distribusi_detail_id' => 'required_with:items_bahan_baku|exists:dist_detail_bahan_bakus,id',
            'items_bahan_baku.*.qty_diterima' => 'required_with:items_bahan_baku|numeric|min:0',
            'items_bahan_baku.*.kondisi' => 'required_with:items_bahan_baku|in:baik,cacat',
            'items_mesin' => 'sometimes|array',
            'items_mesin.*.distribusi_detail_id' => 'required_with:items_mesin|exists:dist_detail_mesins,id',
            'items_mesin.*.nomor_seri' => 'nullable|string',
            'items_mesin.*.qty_diterima' => 'required_with:items_mesin|integer|min:1',
            'items_mesin.*.kondisi' => 'required_with:items_mesin|in:baik,cacat',
        ]);

        $penerimaan = DB::transaction(function () use ($request) {
            $penerimaan = PenerimaanBarang::create([
                'distribusi_barang_id' => $request->distribusi_barang_id,
                'tanggal_terima' => $request->tanggal_terima,
                'subtotal' => $request->subtotal,
                'diskon' => $request->diskon,
                'ppn' => $request->ppn,
                'total_bayar' => $request->total_bayar,
            ]);

            $hasCacat = false;
            $menungguStatus = Status::where('konteks', 'retur_barang')->where('kode', 'menunggu_pengganti')->first();

            if ($request->filled('items_bahan_baku')) {
                foreach ($request->items_bahan_baku as $item) {
                    PenerimaanDetailBahanBaku::create([
                        'penerimaan_barang_id' => $penerimaan->id,
                        'distribusi_detail_id' => $item['distribusi_detail_id'],
                        'qty_diterima' => $item['qty_diterima'],
                        'kondisi' => $item['kondisi'],
                    ]);
                    if ($item['kondisi'] === 'cacat') $hasCacat = true;
                }
            }

            if ($request->filled('items_mesin')) {
                foreach ($request->items_mesin as $item) {
                    PenerimaanDetailMesin::create([
                        'penerimaan_barang_id' => $penerimaan->id,
                        'distribusi_detail_id' => $item['distribusi_detail_id'],
                        'nomor_seri' => $item['nomor_seri'] ?? null,
                        'qty_diterima' => (int) $item['qty_diterima'],
                        'kondisi' => $item['kondisi'],
                    ]);
                    if ($item['kondisi'] === 'cacat') $hasCacat = true;
                }
            }

            // Update stok pusat (hanya kondisi baik)
            $this->stockService->updateFromReceipt($penerimaan);

            // Update distribusi status
            if ($hasCacat) {
                $dikirimSebagianStatus = Status::where('konteks', 'distribusi_barang')->where('kode', 'dikirim_sebagian')->first();
                $penerimaan->distribusiBarang->update(['status_id' => $dikirimSebagianStatus?->id]);

                // Auto buat retur untuk barang cacat
                $this->autoCreateRetur($penerimaan, $menungguStatus);
            } else {
                $diterimaStatus = Status::where('konteks', 'distribusi_barang')->where('kode', 'diterima')->first();
                $penerimaan->distribusiBarang->update(['status_id' => $diterimaStatus?->id]);
            }

            // Cek apakah PO sudah selesai (semua distribusi diterima + tidak ada retur aktif)
            $this->checkPoSelesai($penerimaan->distribusiBarang->po);

            return $penerimaan;
        });

        return response()->json(['message' => 'Penerimaan berhasil dicatat.', 'data' => $this->formatPenerimaan($penerimaan->fresh()->load(['distribusiBarang.po', 'detailBahanBakus', 'detailMesins']))], 201);
    }

    public function show(PenerimaanBarang $penerimaan): JsonResponse
    {
        $penerimaan->load(['distribusiBarang.po', 'detailBahanBakus.distribusiDetail.poItem.bahanBaku', 'detailMesins.distribusiDetail.poItem.mesin']);
        return response()->json(['data' => $this->formatPenerimaan($penerimaan)]);
    }

    /**
     * PRD §4.7: PO selesai = semua distribusi diterima + tidak ada retur aktif
     */
    private function checkPoSelesai($po): void
    {
        // Semua distribusi harus berstatus 'diterima'
        $allDiterima = $po->distribusiBarangs()
            ->whereHas('status', fn ($q) => $q->where('kode', 'diterima'))
            ->count();
        $totalDist = $po->distribusiBarangs()->count();

        if ($totalDist === 0 || $allDiterima < $totalDist) return;

        // Tidak ada retur aktif (menunggu_pengganti atau pengganti_dikirim)
        $activeRetur = \App\Models\ReturBarang::whereHas('penerimaanBarang', fn ($q) => $q->where('distribusi_barang_id', $po->distribusiBarangs()->pluck('id')))
            ->whereHas('status', fn ($q) => $q->whereIn('kode', ['menunggu_pengganti', 'pengganti_dikirim']))
            ->count();

        if ($activeRetur > 0) return;

        // Semua syarat terpenuhi → PO selesai
        $selesaiStatus = Status::where('konteks', 'purchase_order')->where('kode', 'selesai')->first();
        $po->update(['status_id' => $selesaiStatus?->id]);
    }

    private function autoCreateRetur(PenerimaanBarang $penerimaan, ?Status $menungguStatus): void
    {
        $retur = ReturBarang::create([
            'penerimaan_barang_id' => $penerimaan->id,
            'tanggal_retur' => now(),
            'status_id' => $menungguStatus?->id,
        ]);

        foreach ($penerimaan->detailBahanBakus as $detail) {
            if ($detail->kondisi === 'cacat') {
                ReturDetailBahanBaku::create([
                    'retur_barang_id' => $retur->id,
                    'penerimaan_detail_id' => $detail->id,
                    'qty_retur' => $detail->qty_diterima,
                    'alasan' => 'Barang cacat saat penerimaan',
                    'status_id' => $menungguStatus?->id,
                ]);
            }
        }

        foreach ($penerimaan->detailMesins as $detail) {
            if ($detail->kondisi === 'cacat') {
                ReturDetailMesin::create([
                    'retur_barang_id' => $retur->id,
                    'penerimaan_detail_id' => $detail->id,
                    'qty_retur' => $detail->qty_diterima,
                    'alasan' => 'Barang cacat saat penerimaan',
                    'nomor_seri' => $detail->nomor_seri,
                    'status_id' => $menungguStatus?->id,
                ]);
            }
        }
    }

    private function formatPenerimaan($p): array
    {
        return [
            'id' => $p->id, 'nomor_penerimaan' => $p->nomor_penerimaan,
            'distribusi_barang' => $p->distribusiBarang ? ['id' => $p->distribusiBarang->id, 'nomor_distribusi' => $p->distribusiBarang->nomor_distribusi, 'po' => $p->distribusiBarang->po ? ['nomor_po' => $p->distribusiBarang->po->nomor_po] : null] : null,
            'tanggal_terima' => $p->tanggal_terima?->format('Y-m-d'),
            'subtotal' => (float) $p->subtotal, 'diskon' => (float) $p->diskon, 'ppn' => (float) $p->ppn, 'total_bayar' => (float) $p->total_bayar,
            'items_bahan_baku' => $p->detailBahanBakus->map(fn ($i) => ['id' => $i->id, 'qty_diterima' => (float) $i->qty_diterima, 'kondisi' => $i->kondisi]),
            'items_mesin' => $p->detailMesins->map(fn ($i) => ['id' => $i->id, 'nomor_seri' => $i->nomor_seri, 'qty_diterima' => $i->qty_diterima, 'kondisi' => $i->kondisi]),
            'created_at' => $p->created_at?->toDateTimeString(),
        ];
    }
}
