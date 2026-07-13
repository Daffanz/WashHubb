<?php

namespace App\Http\Controllers\Api\Procurement;

use App\Http\Controllers\Controller;
use App\Models\PenerimaanBarang;
use App\Models\PenerimaanDetailBahanBaku;
use App\Models\PenerimaanDetailMesin;
use App\Models\Status;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PenerimaanController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $penerimaans = PenerimaanBarang::with(['distribusiBarang.po', 'status', 'detailBahanBakus.distribusiDetail.poItem.bahanBaku', 'detailMesins.distribusiDetail.poItem.mesin'])->paginate(15);
        return response()->json([
            'data' => $penerimaans->map(fn ($p) => $this->formatPenerimaan($p)),
            'meta' => ['current_page' => $penerimaans->currentPage(), 'last_page' => $penerimaans->lastPage(), 'per_page' => $penerimaans->perPage(), 'total' => $penerimaans->total()],
        ]);
    }

    /**
     * UC-34: Tim Pengadaan catat penerimaan
     * - User input qty_diterima + kondisi (baik/cacat)
     * - Yang baik → stok supplier berkurang
     * - Yang cacat → tidak kurangi stok, nanti di retur
     * - Stok perusahaan TIDAK bertambah di sini (baru saat mutasi)
     */
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
            $stockService = new \App\Services\StockService();

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

            // Load relasi untuk StockService
            $penerimaan->load([
                'detailBahanBakus.distribusiDetail.poItem',
                'detailMesins.distribusiDetail.poItem',
            ]);

            // Kurangi stok supplier (hanya yang baik) — stok perusahaan TIDAK di sini
            $stockService->decreaseSupplierStockFromReceipt($penerimaan);

            // Set status receipt
            if ($hasCacat) {
                $menungguRetur = Status::where('konteks', 'penerimaan_barang')->where('kode', 'menunggu_retur')->first();
                $penerimaan->update(['status_id' => $menungguRetur?->id]);
            } else {
                $selesai = Status::where('konteks', 'penerimaan_barang')->where('kode', 'selesai')->first();
                $penerimaan->update(['status_id' => $selesai?->id]);
            }

            // Update distribusi status → diterima
            $diterimaDist = Status::where('konteks', 'distribusi_barang')->where('kode', 'diterima')->first();
            $penerimaan->distribusiBarang->update(['status_id' => $diterimaDist?->id]);

            // Cek PO selesai
            $this->checkPoSelesai($penerimaan->distribusiBarang->po);

            return $penerimaan;
        });

        return response()->json(['message' => 'Penerimaan berhasil dicatat.', 'data' => $this->formatPenerimaan($penerimaan->fresh()->load(['distribusiBarang.po', 'status', 'detailBahanBakus.distribusiDetail.poItem.bahanBaku', 'detailMesins.distribusiDetail.poItem.mesin']))], 201);
    }

    public function show(PenerimaanBarang $penerimaan): JsonResponse
    {
        $penerimaan->load(['distribusiBarang.po', 'status', 'detailBahanBakus.distribusiDetail.poItem.bahanBaku', 'detailMesins.distribusiDetail.poItem.mesin', 'returBarangs']);
        return response()->json(['data' => $this->formatPenerimaan($penerimaan)]);
    }

    private function checkPoSelesai($po): void
    {
        $allDiterima = $po->distribusiBarangs()
            ->whereHas('status', fn ($q) => $q->where('kode', 'diterima'))
            ->count();
        $totalDist = $po->distribusiBarangs()->count();

        if ($totalDist === 0 || $allDiterima < $totalDist) return;

        $activeRetur = \App\Models\ReturBarang::whereHas('penerimaanBarang', fn ($q) => $q->where('distribusi_barang_id', $po->distribusiBarangs()->pluck('id')))
            ->whereHas('status', fn ($q) => $q->whereIn('kode', ['menunggu_pengganti', 'pengganti_dikirim']))
            ->count();

        if ($activeRetur > 0) return;

        $selesaiStatus = Status::where('konteks', 'purchase_order')->where('kode', 'selesai')->first();
        $po->update(['status_id' => $selesaiStatus?->id]);
    }

    private function formatPenerimaan($p): array
    {
        return [
            'id' => $p->id, 'nomor_penerimaan' => $p->nomor_penerimaan,
            'distribusi_barang' => $p->distribusiBarang ? ['id' => $p->distribusiBarang->id, 'nomor_distribusi' => $p->distribusiBarang->nomor_distribusi, 'po' => $p->distribusiBarang->po ? ['nomor_po' => $p->distribusiBarang->po->nomor_po] : null] : null,
            'tanggal_terima' => $p->tanggal_terima?->format('Y-m-d'),
            'subtotal' => (float) $p->subtotal, 'diskon' => (float) $p->diskon, 'ppn' => (float) $p->ppn, 'total_bayar' => (float) $p->total_bayar,
            'status' => $p->status ? ['id' => $p->status->id, 'kode' => $p->status->kode, 'label' => $p->status->label] : null,
            'items_bahan_baku' => $p->detailBahanBakus->map(fn ($i) => ['id' => $i->id, 'nama' => $i->distribusiDetail?->poItem?->bahanBaku?->nama ?? '-', 'qty_diterima' => (float) $i->qty_diterima, 'kondisi' => $i->kondisi]),
            'items_mesin' => $p->detailMesins->map(fn ($i) => ['id' => $i->id, 'nama' => $i->distribusiDetail?->poItem?->mesin?->nama ?? '-', 'nomor_seri' => $i->nomor_seri, 'qty_diterima' => $i->qty_diterima, 'kondisi' => $i->kondisi]),
            'retur' => $p->returBarangs->first() ? ['id' => $p->returBarangs->first()->id, 'status' => $p->returBarangs->first()->status?->kode] : null,
            'created_at' => $p->created_at?->toDateTimeString(),
        ];
    }
}
