<?php

namespace App\Http\Controllers\Api\Procurement;

use App\Http\Controllers\Controller;
use App\Models\DistribusiBarang;
use App\Models\DistDetailBahanBaku;
use App\Models\DistDetailMesin;
use App\Models\Status;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DistribusiController extends Controller
{
    public function index(): JsonResponse
    {
        $distribusis = DistribusiBarang::with(['po.supplier.user', 'status', 'detailBahanBakus.poItem.bahanBaku', 'detailMesins.poItem.mesin'])->paginate(15);
        return response()->json([
            'data' => $distribusis->map(fn ($d) => $this->formatDistribusi($d)),
            'meta' => ['current_page' => $distribusis->currentPage(), 'last_page' => $distribusis->lastPage(), 'per_page' => $distribusis->perPage(), 'total' => $distribusis->total()],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'po_id' => 'required|exists:purchase_orders,id',
            'tanggal_kirim' => 'required|date',
            'items_bahan_baku' => 'sometimes|array',
            'items_bahan_baku.*.po_item_id' => 'required_with:items_bahan_baku|exists:purchase_order_item_bahan_bakus,id',
            'items_bahan_baku.*.jumlah_kirim' => 'required_with:items_bahan_baku|numeric|min:0.0001',
            'items_mesin' => 'sometimes|array',
            'items_mesin.*.po_item_id' => 'required_with:items_mesin|exists:purchase_order_item_mesins,id',
            'items_mesin.*.jumlah_kirim' => 'required_with:items_mesin|integer|min:1',
        ]);

        // PRD Rule 7: Blokir distribusi baru jika ada yang belum dikonfirmasi
        $pendingDist = DistribusiBarang::where('po_id', $request->po_id)
            ->whereHas('status', fn ($q) => $q->where('kode', 'dikirim'))
            ->count();
        if ($pendingDist > 0) {
            return response()->json(['message' => 'Masih ada distribusi sebelumnya yang belum dikonfirmasi diterima.'], 422);
        }

        // Validate jumlah_kirim ≤ qty_disetujui
        if ($request->filled('items_bahan_baku')) {
            foreach ($request->items_bahan_baku as $item) {
                $poItem = \App\Models\PurchaseOrderItemBahanBaku::find($item['po_item_id']);
                if ($poItem && $item['jumlah_kirim'] > $poItem->qty_disetujui) {
                    return response()->json(['message' => "Jumlah kirim ({$item['jumlah_kirim']}) melebihi jumlah disetujui ({$poItem->qty_disetujui}) untuk item {$poItem->bahanBaku?->nama}."], 422);
                }
            }
        }
        if ($request->filled('items_mesin')) {
            foreach ($request->items_mesin as $item) {
                $poItem = \App\Models\PurchaseOrderItemMesin::find($item['po_item_id']);
                if ($poItem && $item['jumlah_kirim'] > $poItem->qty_disetujui) {
                    return response()->json(['message' => "Jumlah kirim ({$item['jumlah_kirim']}) melebihi jumlah disetujui ({$poItem->qty_disetujui}) untuk item {$poItem->mesin?->nama}."], 422);
                }
            }
        }

        $distribusi = DB::transaction(function () use ($request) {
            $dikirimStatus = Status::where('konteks', 'distribusi_barang')->where('kode', 'dikirim')->first();
            $distribusi = DistribusiBarang::create([
                'po_id' => $request->po_id,
                'tanggal_kirim' => $request->tanggal_kirim,
                'status_id' => $dikirimStatus?->id,
            ]);

            if ($request->filled('items_bahan_baku')) {
                foreach ($request->items_bahan_baku as $item) {
                    DistDetailBahanBaku::create([
                        'distribusi_barang_id' => $distribusi->id,
                        'po_item_id' => $item['po_item_id'],
                        'jumlah_kirim' => $item['jumlah_kirim'],
                    ]);
                }
            }
            if ($request->filled('items_mesin')) {
                foreach ($request->items_mesin as $item) {
                    DistDetailMesin::create([
                        'distribusi_barang_id' => $distribusi->id,
                        'po_item_id' => $item['po_item_id'],
                        'jumlah_kirim' => (int) $item['jumlah_kirim'],
                    ]);
                }
            }

            // Kurangi stok supplier
            \App\Services\StockService::decreaseSupplierStock($distribusi);

            return $distribusi;
        });

        return response()->json(['message' => 'Distribusi berhasil dibuat.', 'data' => $this->formatDistribusi($distribusi->load(['po.supplier.user', 'status', 'detailBahanBakus.poItem', 'detailMesins.poItem']))], 201);
    }

    public function show(DistribusiBarang $distribusi): JsonResponse
    {
        $distribusi->load(['po.supplier.user', 'status', 'detailBahanBakus.poItem.bahanBaku', 'detailMesins.poItem.mesin', 'penerimaanBarangs']);
        return response()->json(['data' => $this->formatDistribusi($distribusi)]);
    }

    private function formatDistribusi($d): array
    {
        return [
            'id' => $d->id, 'nomor_distribusi' => $d->nomor_distribusi,
            'po' => $d->po ? ['id' => $d->po->id, 'nomor_po' => $d->po->nomor_po, 'jenis_po' => $d->po->jenis_po] : null,
            'tanggal_kirim' => $d->tanggal_kirim?->format('Y-m-d'),
            'status' => $d->status ? ['id' => $d->status->id, 'kode' => $d->status->kode, 'label' => $d->status->label] : null,
            'items_bahan_baku' => $d->detailBahanBakus->map(fn ($i) => ['id' => $i->id, 'po_item_id' => $i->po_item_id, 'nama' => $i->poItem?->bahanBaku?->nama, 'jumlah_kirim' => (float) $i->jumlah_kirim, 'qty_disetujui' => $i->poItem?->qty_disetujui]),
            'items_mesin' => $d->detailMesins->map(fn ($i) => ['id' => $i->id, 'po_item_id' => $i->po_item_id, 'nama' => $i->poItem?->mesin?->nama, 'jumlah_kirim' => $i->jumlah_kirim, 'qty_disetujui' => $i->poItem?->qty_disetujui]),
            'created_at' => $d->created_at?->toDateTimeString(),
        ];
    }
}
