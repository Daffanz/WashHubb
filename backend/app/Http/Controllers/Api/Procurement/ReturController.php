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
    /**
     * Supplier hanya lihat retur dari PO miliknya
     */
    public function index(Request $request): JsonResponse
    {
        $query = ReturBarang::with(['penerimaanBarang.distribusiBarang.po.supplier.user', 'status', 'detailBahanBakus.status', 'detailMesins.status']);

        if ($request->user()->hasRole('supplier')) {
            $supplier = $request->user()->suppliers()->first();
            if ($supplier) {
                $query->whereHas('penerimaanBarang.distribusiBarang.po', fn ($q) => $q->where('supplier_id', $supplier->id));
            }
        }

        $returs = $query->orderByDesc('created_at')->paginate(15);
        return response()->json([
            'data' => $returs->map(fn ($r) => $this->formatRetur($r)),
            'meta' => ['current_page' => $returs->currentPage(), 'last_page' => $returs->lastPage(), 'per_page' => $returs->perPage(), 'total' => $returs->total()],
        ]);
    }

    public function show(ReturBarang $retur): JsonResponse
    {
        $retur->load(['penerimaanBarang.distribusiBarang.po.supplier.user', 'status', 'detailBahanBakus.penerimaanDetail', 'detailMesins.penerimaanDetail', 'detailBahanBakus.status', 'detailMesins.status']);
        return response()->json(['data' => $this->formatRetur($retur)]);
    }

    /**
     * Buat retur manual oleh Tim Pengadaan
     * Referensi ke penerimaan_barang yang sudah ada
     */
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

    /**
     * UC-37: Supplier kirim pengganti → retur → pengganti_dikirim
     * Stok supplier berkurang untuk barang pengganti
     */
    public function kirimPengganti(Request $request, ReturBarang $retur): JsonResponse
    {
        if ($retur->status?->kode !== 'menunggu_pengganti') {
            return response()->json(['message' => 'Retur harus berstatus menunggu_pengganti.'], 422);
        }

        $request->validate([
            'items_bahan_baku' => 'sometimes|array',
            'items_bahan_baku.*.retur_detail_id' => 'required|exists:retur_detail_bahan_bakus,id',
            'items_bahan_baku.*.qty_pengganti' => 'required|numeric|min:1',
            'items_mesin' => 'sometimes|array',
            'items_mesin.*.retur_detail_id' => 'required|exists:retur_detail_mesins,id',
            'items_mesin.*.qty_pengganti' => 'required|integer|min:1',
            'items_mesin.*.nomor_seri_pengganti' => 'nullable|string',
        ]);

        $kirimStatus = Status::where('konteks', 'retur_barang')->where('kode', 'pengganti_dikirim')->first();

        DB::transaction(function () use ($request, $retur, $kirimStatus) {
            // Update detail retur
            if ($request->filled('items_bahan_baku')) {
                foreach ($request->items_bahan_baku as $item) {
                    $detail = ReturDetailBahanBaku::where('id', $item['retur_detail_id'])
                        ->where('retur_barang_id', $retur->id)->first();
                    if ($detail) {
                        $detail->update(['qty_pengganti' => $item['qty_pengganti'], 'status_id' => $kirimStatus?->id]);
                    }
                }
            }

            if ($request->filled('items_mesin')) {
                foreach ($request->items_mesin as $item) {
                    $detail = ReturDetailMesin::where('id', $item['retur_detail_id'])
                        ->where('retur_barang_id', $retur->id)->first();
                    if ($detail) {
                        $detail->update([
                            'qty_pengganti' => $item['qty_pengganti'],
                            'nomor_seri_pengganti' => $item['nomor_seri_pengganti'] ?? null,
                            'status_id' => $kirimStatus?->id,
                        ]);
                    }
                }
            }

            $retur->update(['status_id' => $kirimStatus?->id]);
        });

        return response()->json(['message' => 'Pengganti berhasil dikirim.']);
    }

    /**
     * UC-38: Tim Pengadaan konfirmasi
     * - Sesuai → stok perusahaan bertambah, distribusi → diterima, retur → selesai
     * - Tidak sesuai → retur stays menunggu_pengganti, supplier kirim lagi
     */
    public function confirm(Request $request, ReturBarang $retur): JsonResponse
    {
        $request->validate([
            'status' => 'required|in:selesai,ditolak',
            'items_bahan_baku' => 'sometimes|array',
            'items_bahan_baku.*.retur_detail_id' => 'required|exists:retur_detail_bahan_bakus,id',
            'items_bahan_baku.*.qty_pengganti' => 'required|numeric|min:0',
            'items_mesin' => 'sometimes|array',
            'items_mesin.*.retur_detail_id' => 'required|exists:retur_detail_mesins,id',
            'items_mesin.*.qty_pengganti' => 'required|integer|min:0',
        ]);

        DB::transaction(function () use ($request, $retur) {
            if ($request->status === 'selesai') {
                $selesaiStatus = Status::where('konteks', 'retur_barang')->where('kode', 'selesai')->first();
                $retur->update(['status_id' => $selesaiStatus?->id]);

                // Update qty_pengganti final per detail
                if ($request->filled('items_bahan_baku')) {
                    foreach ($request->items_bahan_baku as $item) {
                        ReturDetailBahanBaku::where('id', $item['retur_detail_id'])
                            ->where('retur_barang_id', $retur->id)
                            ->update(['qty_pengganti' => $item['qty_pengganti'], 'status_id' => $selesaiStatus?->id]);
                    }
                }
                if ($request->filled('items_mesin')) {
                    foreach ($request->items_mesin as $item) {
                        ReturDetailMesin::where('id', $item['retur_detail_id'])
                            ->where('retur_barang_id', $retur->id)
                            ->update(['qty_pengganti' => $item['qty_pengganti'], 'status_id' => $selesaiStatus?->id]);
                    }
                }

                // Update receipt status → selesai (stok perusahaan bertambah via MutasiController)
                $selesaiReceipt = Status::where('konteks', 'penerimaan_barang')->where('kode', 'selesai')->first();
                $retur->penerimaanBarang->update(['status_id' => $selesaiReceipt?->id]);

                // Cek PO selesai
                $this->checkPoSelesai($retur->penerimaanBarang->distribusiBarang->po);
            }
            // If ditolak → status stays menunggu_pengganti, supplier kirim lagi
        });

        return response()->json(['message' => 'Retur berhasil dikonfirmasi.']);
    }

    private function checkPoSelesai($po): void
    {
        $allDiterima = $po->distribusiBarangs()
            ->whereHas('status', fn ($q) => $q->where('kode', 'diterima'))
            ->count();
        $totalDist = $po->distribusiBarangs()->count();

        if ($totalDist === 0 || $allDiterima < $totalDist) return;

        $activeRetur = ReturBarang::whereHas('penerimaanBarang', fn ($q) => $q->where('distribusi_barang_id', $po->distribusiBarangs()->pluck('id')))
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
            'po' => $r->penerimaanBarang?->distribusiBarang?->po ? [
                'id' => $r->penerimaanBarang->distribusiBarang->po->id,
                'nomor_po' => $r->penerimaanBarang->distribusiBarang->po->nomor_po,
                'supplier' => $r->penerimaanBarang->distribusiBarang->po->supplier?->user?->nama,
            ] : null,
            'tanggal_retur' => $r->tanggal_retur?->toDateTimeString(),
            'status' => $r->status ? ['id' => $r->status->id, 'kode' => $r->status->kode, 'label' => $r->status->label] : null,
            'detail_bahan_baku' => $r->detailBahanBakus->map(fn ($d) => [
                'id' => $d->id, 'qty_retur' => $d->qty_retur, 'alasan' => $d->alasan,
                'qty_pengganti' => $d->qty_pengganti,
                'status' => $d->status ? ['kode' => $d->status->kode, 'label' => $d->status->label] : null,
            ]),
            'detail_mesin' => $r->detailMesins->map(fn ($d) => [
                'id' => $d->id, 'qty_retur' => $d->qty_retur, 'alasan' => $d->alasan,
                'qty_pengganti' => $d->qty_pengganti, 'nomor_seri_pengganti' => $d->nomor_seri_pengganti,
                'status' => $d->status ? ['kode' => $d->status->kode, 'label' => $d->status->label] : null,
            ]),
            'created_at' => $r->created_at?->toDateTimeString(),
        ];
    }
}
