<?php
namespace App\Http\Controllers\Api\Procurement;

use App\Http\Controllers\Controller;
use App\Models\ReturBarang;
use App\Models\ReturDetailBahanBaku;
use App\Models\ReturDetailMesin;
use App\Models\Status;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReturController extends Controller
{
    public function index(): JsonResponse
    {
        $returs = ReturBarang::with(['penerimaanBarang.distribusiBarang', 'status'])->paginate(15);
        return response()->json([
            'data' => $returs->map(fn ($r) => $this->formatRetur($r)),
            'meta' => ['current_page' => $returs->currentPage(), 'last_page' => $returs->lastPage(), 'per_page' => $returs->perPage(), 'total' => $returs->total()],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'penerimaan_barang_id' => 'required|exists:penerimaan_barangs,id',
            'items_bahan_baku' => 'sometimes|array',
            'items_bahan_baku.*.penerimaan_detail_id' => 'required|exists:penerimaan_detail_bahan_bakus,id',
            'items_bahan_baku.*.qty_retur' => 'required|numeric|min:0.0001',
            'items_bahan_baku.*.alasan' => 'required|string',
            'items_mesin' => 'sometimes|array',
            'items_mesin.*.penerimaan_detail_id' => 'required|exists:penerimaan_detail_mesins,id',
            'items_mesin.*.qty_retur' => 'required|integer|min:1',
            'items_mesin.*.alasan' => 'required|string',
        ]);

        $retur = DB::transaction(function () use ($request) {
            $menungguStatus = Status::where('konteks', 'retur_barang')->where('kode', 'menunggu_pengganti')->first();
            $retur = ReturBarang::create([
                'penerimaan_barang_id' => $request->penerimaan_barang_id,
                'tanggal_retur' => now(),
                'status_id' => $menungguStatus?->id,
            ]);

            if ($request->filled('items_bahan_baku')) {
                foreach ($request->items_bahan_baku as $item) {
                    ReturDetailBahanBaku::create([
                        'retur_barang_id' => $retur->id,
                        'penerimaan_detail_id' => $item['penerimaan_detail_id'],
                        'qty_retur' => $item['qty_retur'],
                        'alasan' => $item['alasan'],
                        'foto_bukti' => $item['foto_bukti'] ?? null,
                        'status_id' => $menungguStatus?->id,
                    ]);
                }
            }

            if ($request->filled('items_mesin')) {
                foreach ($request->items_mesin as $item) {
                    ReturDetailMesin::create([
                        'retur_barang_id' => $retur->id,
                        'penerimaan_detail_id' => $item['penerimaan_detail_id'],
                        'qty_retur' => $item['qty_retur'],
                        'alasan' => $item['alasan'],
                        'foto_bukti' => $item['foto_bukti'] ?? null,
                        'status_id' => $menungguStatus?->id,
                    ]);
                }
            }

            return $retur;
        });

        return response()->json([
            'message' => 'Retur berhasil dibuat.',
            'data' => $this->formatRetur($retur->load(['penerimaanBarang', 'status', 'detailBahanBakus', 'detailMesins'])),
        ], 201);
    }

    public function show(ReturBarang $retur): JsonResponse
    {
        return response()->json([
            'data' => $this->formatRetur($retur->load(['penerimaanBarang.distribusiBarang', 'status', 'detailBahanBakus.penerimaanDetail', 'detailMesins.penerimaanDetail'])),
        ]);
    }

    public function confirm(Request $request, ReturBarang $retur): JsonResponse
    {
        $request->validate(['status' => 'required|in:selesai,ditolak']);

        $stockService = new \App\Services\StockService();

        DB::transaction(function () use ($request, $retur, $stockService) {
            if ($request->status === 'selesai') {
                $selesaiStatus = Status::where('konteks', 'retur_barang')->where('kode', 'selesai')->first();
                $retur->update(['status_id' => $selesaiStatus?->id]);
                $retur->detailBahanBakus()->update(['status_id' => $selesaiStatus?->id]);
                $retur->detailMesins()->update(['status_id' => $selesaiStatus?->id]);

                // Stok pusat bertambah untuk barang pengganti
                foreach ($retur->detailBahanBakus as $detail) {
                    if ($detail->qty_pengganti > 0) {
                        $stockService->increaseStockFromRetur($detail);
                    }
                }
                foreach ($retur->detailMesins as $detail) {
                    if ($detail->qty_pengganti > 0) {
                        $stockService->increaseStockFromRetur($detail);
                    }
                }

                // Update distribusi status → diterima
                $diterimaStatus = Status::where('konteks', 'distribusi_barang')->where('kode', 'diterima')->first();
                $retur->penerimaanBarang->distribusiBarang->update(['status_id' => $diterimaStatus?->id]);

                // Cek apakah PO selesai
                $this->checkPoSelesai($retur->penerimaanBarang->distribusiBarang->po);
            }
            // If ditolak, status stays menunggu_pengganti
        });

        return response()->json(['message' => 'Retur berhasil dikonfirmasi.']);
    }

    /**
     * PRD §4.7: PO selesai = semua distribusi diterima + tidak ada retur aktif
     */
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

    private function formatRetur($r): array
    {
        return [
            'id' => $r->id,
            'penerimaan_barang_id' => $r->penerimaan_barang_id,
            'tanggal_retur' => $r->tanggal_retur?->toDateTimeString(),
            'status' => $r->status ? ['id' => $r->status->id, 'kode' => $r->status->kode, 'label' => $r->status->label] : null,
            'detail_bahan_baku' => $r->detailBahanBakus->map(fn ($d) => ['id' => $d->id, 'qty_retur' => $d->qty_retur, 'alasan' => $d->alasan, 'qty_pengganti' => $d->qty_pengganti]),
            'detail_mesin' => $r->detailMesins->map(fn ($d) => ['id' => $d->id, 'qty_retur' => $d->qty_retur, 'alasan' => $d->alasan, 'qty_pengganti' => $d->qty_pengganti, 'nomor_seri_pengganti' => $d->nomor_seri_pengganti]),
            'created_at' => $r->created_at?->toDateTimeString(),
        ];
    }
}
