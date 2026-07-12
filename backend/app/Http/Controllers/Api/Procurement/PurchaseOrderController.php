<?php

namespace App\Http\Controllers\Api\Procurement;

use App\Http\Controllers\Controller;
use App\Models\DistribusiBarang;
use App\Models\DistDetailBahanBaku;
use App\Models\DistDetailMesin;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItemBahanBaku;
use App\Models\PurchaseOrderItemMesin;
use App\Models\Status;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseOrderController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = PurchaseOrder::with(['supplier.user', 'status', 'itemBahanBakus.bahanBaku', 'itemMesins.mesin', 'itemBahanBakus.status', 'itemMesins.status']);
        if ($request->user()->hasRole('supplier')) {
            $supplier = $request->user()->suppliers()->first();
            if ($supplier) $query->where('supplier_id', $supplier->id);
        }
        if ($request->filled('status')) {
            $query->whereHas('status', fn ($q) => $q->where('kode', $request->status));
        }
        $poList = $query->orderByDesc('created_at')->paginate(15);
        return response()->json([
            'data' => $poList->map(fn ($po) => $this->formatPO($po)),
            'meta' => ['current_page' => $poList->currentPage(), 'last_page' => $poList->lastPage(), 'per_page' => $poList->perPage(), 'total' => $poList->total()],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'jenis_po' => 'required|in:bahan_baku,mesin',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|integer',
            'items.*.jumlah' => 'required|numeric|min:1',
            'items.*.harga_satuan' => 'required|numeric|min:0',
        ]);

        $po = DB::transaction(function () use ($request) {
            $diajukanStatus = Status::where('konteks', 'purchase_order')->where('kode', 'diajukan')->first();
            $po = PurchaseOrder::create([
                'supplier_id' => $request->supplier_id,
                'dibuat_oleh_id' => $request->user()->id,
                'jenis_po' => $request->jenis_po,
                'status_id' => $diajukanStatus->id,
            ]);

            $itemStatus = Status::where('konteks', 'purchase_order_item')->where('kode', 'disetujui')->first();
            $totalNilai = 0;
            foreach ($request->items as $item) {
                $totalNilai += $item['jumlah'] * $item['harga_satuan'];
                if ($request->jenis_po === 'bahan_baku') {
                    PurchaseOrderItemBahanBaku::create([
                        'po_id' => $po->id, 'bahan_baku_id' => $item['item_id'],
                        'jumlah' => $item['jumlah'], 'harga_satuan' => $item['harga_satuan'],
                        'status_id' => $itemStatus->id,
                    ]);
                } else {
                    PurchaseOrderItemMesin::create([
                        'po_id' => $po->id, 'mesin_id' => $item['item_id'],
                        'jumlah' => (int) $item['jumlah'], 'harga_satuan' => $item['harga_satuan'],
                        'status_id' => $itemStatus->id,
                    ]);
                }
            }
            $po->update(['total_nilai' => $totalNilai]);
            return $po;
        });

        return response()->json(['message' => 'PO berhasil dibuat.', 'data' => $this->formatPO($po->fresh()->load(['supplier.user', 'status', 'itemBahanBakus.bahanBaku', 'itemMesins.mesin', 'itemBahanBakus.status', 'itemMesins.status']))], 201);
    }

    public function show(PurchaseOrder $purchaseOrder): JsonResponse
    {
        $purchaseOrder->load(['supplier.user', 'status', 'itemBahanBakus.bahanBaku', 'itemMesins.mesin', 'itemBahanBakus.status', 'itemMesins.status', 'distribusiBarangs.status', 'distribusiBarangs.detailBahanBakus', 'distribusiBarangs.detailMesins']);
        return response()->json(['data' => $this->formatPO($purchaseOrder)]);
    }

    public function update(Request $request, PurchaseOrder $purchaseOrder): JsonResponse
    {
        if ($purchaseOrder->status?->kode !== 'diajukan') {
            return response()->json(['message' => 'Hanya PO diajukan yang bisa diedit.'], 422);
        }
        DB::transaction(function () use ($request, $purchaseOrder) {
            $purchaseOrder->update($request->only(['supplier_id', 'jenis_po']));
            if ($request->filled('items')) {
                $purchaseOrder->itemBahanBakus()->delete();
                $purchaseOrder->itemMesins()->delete();
                $itemStatus = Status::where('konteks', 'purchase_order_item')->where('kode', 'disetujui')->first();
                $totalNilai = 0;
                foreach ($request->items as $item) {
                    $totalNilai += $item['jumlah'] * $item['harga_satuan'];
                    if ($purchaseOrder->jenis_po === 'bahan_baku') {
                        PurchaseOrderItemBahanBaku::create(['po_id' => $purchaseOrder->id, 'bahan_baku_id' => $item['item_id'], 'jumlah' => $item['jumlah'], 'harga_satuan' => $item['harga_satuan'], 'status_id' => $itemStatus?->id]);
                    } else {
                        PurchaseOrderItemMesin::create(['po_id' => $purchaseOrder->id, 'mesin_id' => $item['item_id'], 'jumlah' => (int) $item['jumlah'], 'harga_satuan' => $item['harga_satuan'], 'status_id' => $itemStatus?->id]);
                    }
                }
                $purchaseOrder->update(['total_nilai' => $totalNilai]);
            }
        });
        return response()->json(['message' => 'PO berhasil diperbarui.', 'data' => $this->formatPO($purchaseOrder->fresh()->load(['supplier.user', 'status', 'itemBahanBakus.bahanBaku', 'itemMesins.mesin', 'itemBahanBakus.status', 'itemMesins.status']))]);
    }

    public function destroy(PurchaseOrder $purchaseOrder): JsonResponse
    {
        if ($purchaseOrder->status?->kode !== 'diajukan') {
            return response()->json(['message' => 'Hanya PO diajukan yang bisa dihapus.'], 422);
        }
        DB::transaction(function () use ($purchaseOrder) {
            $purchaseOrder->itemBahanBakus()->delete();
            $purchaseOrder->itemMesins()->delete();
            $purchaseOrder->delete();
        });
        return response()->json(['message' => 'PO berhasil dihapus.']);
    }

    /**
     * UC-30: Tim Pengadaan kirim PO ke Supplier
     * PO diajukan → dikirim, validasi minimal 1 item
     */
    public function kirim(PurchaseOrder $purchaseOrder): JsonResponse
    {
        if ($purchaseOrder->status?->kode !== 'diajukan') {
            return response()->json(['message' => 'Hanya PO diajukan yang bisa dikirim.'], 422);
        }
        $hasItems = $purchaseOrder->itemBahanBakus()->count() > 0 || $purchaseOrder->itemMesins()->count() > 0;
        if (! $hasItems) {
            return response()->json(['message' => 'PO harus memiliki minimal satu item.'], 422);
        }
        $dikirimStatus = Status::where('konteks', 'purchase_order')->where('kode', 'dikirim')->first();
        $purchaseOrder->update(['status_id' => $dikirimStatus?->id]);
        return response()->json(['message' => 'PO berhasil dikirim ke supplier.', 'data' => $this->formatPO($purchaseOrder->fresh()->load(['supplier.user', 'status', 'itemBahanBakus.bahanBaku', 'itemMesins.mesin', 'itemBahanBakus.status', 'itemMesins.status']))]);
    }

    /**
     * UC-31: Supplier validasi per-item
     * Update item status + qty_disetujui + alasan
     * Rollup PO status dari items
     * TIDAK auto-create distribusi (supplier buat sendiri)
     */
    public function validate(Request $request, PurchaseOrder $purchaseOrder): JsonResponse
    {
        if ($purchaseOrder->status?->kode !== 'dikirim') {
            return response()->json(['message' => 'PO harus berstatus dikirim untuk divalidasi.'], 422);
        }

        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|integer',
            'items.*.status_kode' => 'required|in:disetujui,disetujui_sebagian,ditolak',
            'items.*.qty_disetujui' => 'required_if:items.*.status_kode,disetujui,disetujui_sebagian|nullable|numeric|min:0',
            'items.*.alasan' => 'required_if:items.*.status_kode,ditolak,disetujui_sebagian|nullable|string',
        ]);

        DB::transaction(function () use ($request, $purchaseOrder) {
            foreach ($request->items as $item) {
                $poItem = $purchaseOrder->jenis_po === 'bahan_baku'
                    ? PurchaseOrderItemBahanBaku::where('po_id', $purchaseOrder->id)->where('id', $item['item_id'])->first()
                    : PurchaseOrderItemMesin::where('po_id', $purchaseOrder->id)->where('id', $item['item_id'])->first();

                if (! $poItem) abort(422, 'Item PO tidak ditemukan.');

                $itemStatus = Status::where('konteks', 'purchase_order_item')->where('kode', $item['status_kode'])->first();
                $updateData = ['status_id' => $itemStatus?->id];

                if ($item['status_kode'] === 'ditolak') {
                    $updateData['qty_disetujui'] = 0;
                    $updateData['alasan'] = $item['alasan'];
                } elseif ($item['status_kode'] === 'disetujui_sebagian') {
                    $updateData['qty_disetujui'] = $item['qty_disetujui'];
                    $updateData['alasan'] = $item['alasan'];
                } else {
                    $updateData['qty_disetujui'] = $poItem->jumlah;
                }

                $poItem->update($updateData);
            }

            // Rollup PO status
            $this->rollupPoStatus($purchaseOrder);
        });

        return response()->json([
            'message' => 'Validasi berhasil.',
            'data' => $this->formatPO($purchaseOrder->fresh()->load(['supplier.user', 'status', 'itemBahanBakus.bahanBaku', 'itemMesins.mesin', 'itemBahanBakus.status', 'itemMesins.status'])),
        ]);
    }

    /**
     * Rollup PO status dari status semua item (alur.md 3.3):
     * - Semua disetujui → PO disetujui
     * - Semua ditolak → PO ditolak
     * - Campuran → PO disetujui_sebagian
     */
    private function rollupPoStatus(PurchaseOrder $po): void
    {
        $allItems = $po->itemBahanBakus->merge($po->itemMesins);
        $statusKodes = $allItems->pluck('status')->filter()->pluck('kode')->unique();

        if ($statusKodes->isEmpty()) return;

        $poStatusKode = match (true) {
            $statusKodes->every(fn ($k) => $k === 'disetujui') => 'disetujui',
            $statusKodes->every(fn ($k) => $k === 'ditolak') => 'ditolak',
            default => 'disetujui_sebagian',
        };

        $status = Status::where('konteks', 'purchase_order')->where('kode', $poStatusKode)->first();
        $po->update(['status_id' => $status?->id]);
    }

    private function formatPO($po): array
    {
        return [
            'id' => $po->id, 'nomor_po' => $po->nomor_po,
            'supplier' => $po->supplier ? ['id' => $po->supplier->id, 'nama' => $po->supplier->user?->nama] : null,
            'dibuat_oleh' => $po->dibuatOleh ? ['id' => $po->dibuatOleh->id, 'nama' => $po->dibuatOleh->nama] : null,
            'jenis_po' => $po->jenis_po, 'total_nilai' => (float) $po->total_nilai,
            'status' => $po->status ? ['id' => $po->status->id, 'kode' => $po->status->kode, 'label' => $po->status->label] : null,
            'items_bahan_baku' => $po->itemBahanBakus->map(fn ($i) => [
                'id' => $i->id, 'nama' => $i->bahanBaku?->nama,
                'jumlah' => (float) $i->jumlah, 'harga_satuan' => (float) $i->harga_satuan,
                'qty_disetujui' => $i->qty_disetujui !== null ? (float) $i->qty_disetujui : null,
                'status' => $i->status ? $i->status->kode : null, 'alasan' => $i->alasan,
            ]),
            'items_mesin' => $po->itemMesins->map(fn ($i) => [
                'id' => $i->id, 'nama' => $i->mesin?->nama,
                'jumlah' => $i->jumlah, 'harga_satuan' => (float) $i->harga_satuan,
                'qty_disetujui' => $i->qty_disetujui,
                'status' => $i->status ? $i->status->kode : null, 'alasan' => $i->alasan,
            ]),
            'distribusi' => $po->distribusiBarangs->map(fn ($d) => [
                'id' => $d->id, 'nomor_distribusi' => $d->nomor_distribusi,
                'status' => $d->status?->kode,
            ]),
            'created_at' => $po->created_at?->toDateTimeString(),
        ];
    }
}
