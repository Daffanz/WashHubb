<?php

namespace App\Http\Controllers\Api\Procurement;

use App\Http\Controllers\Controller;
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
        $query = PurchaseOrder::with(['supplier.user', 'status', 'itemBahanBakus.bahanBaku', 'itemMesins.mesin']);
        if ($request->user()->hasRole('supplier')) {
            $supplier = $request->user()->suppliers()->first();
            if ($supplier) $query->where('supplier_id', $supplier->id);
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
            if (! $diajukanStatus) {
                abort(500, 'Status diajukan tidak ditemukan.');
            }
            $po = PurchaseOrder::create([
                'supplier_id' => $request->supplier_id,
                'dibuat_oleh_id' => $request->user()->id,
                'jenis_po' => $request->jenis_po,
                'status_id' => $diajukanStatus->id,
            ]);

            $totalNilai = 0;
            foreach ($request->items as $item) {
                $totalNilai += $item['jumlah'] * $item['harga_satuan'];
                if ($request->jenis_po === 'bahan_baku') {
                    PurchaseOrderItemBahanBaku::create([
                        'po_id' => $po->id, 'bahan_baku_id' => $item['item_id'],
                        'jumlah' => $item['jumlah'], 'harga_satuan' => $item['harga_satuan'],
                        'status_id' => $diajukanStatus->id,
                    ]);
                } else {
                    PurchaseOrderItemMesin::create([
                        'po_id' => $po->id, 'mesin_id' => $item['item_id'],
                        'jumlah' => (int) $item['jumlah'], 'harga_satuan' => $item['harga_satuan'],
                        'status_id' => $diajukanStatus->id,
                    ]);
                }
            }
            $po->update(['total_nilai' => $totalNilai]);
            return $po;
        });

        return response()->json(['message' => 'PO berhasil dibuat.', 'data' => $this->formatPO($po->fresh()->load(['supplier.user', 'status', 'itemBahanBakus.bahanBaku', 'itemMesins.mesin']))], 201);
    }

    public function show(PurchaseOrder $purchaseOrder): JsonResponse
    {
        $purchaseOrder->load(['supplier.user', 'status', 'itemBahanBakus.bahanBaku', 'itemMesins.mesin', 'itemBahanBakus.status', 'itemMesins.status']);
        return response()->json(['data' => $this->formatPO($purchaseOrder)]);
    }

    public function update(Request $request, PurchaseOrder $purchaseOrder): JsonResponse
    {
        if ($purchaseOrder->status?->kode !== 'diajukan') return response()->json(['message' => 'Hanya PO diajukan yang bisa diedit.'], 422);
        DB::transaction(function () use ($request, $purchaseOrder) {
            $purchaseOrder->update($request->only(['supplier_id', 'jenis_po']));
            if ($request->filled('items')) {
                $purchaseOrder->itemBahanBakus()->delete();
                $purchaseOrder->itemMesins()->delete();
                $diajukanStatus = Status::where('konteks', 'purchase_order')->where('kode', 'diajukan')->first();
                $totalNilai = 0;
                foreach ($request->items as $item) {
                    $totalNilai += $item['jumlah'] * $item['harga_satuan'];
                    if ($purchaseOrder->jenis_po === 'bahan_baku') {
                        PurchaseOrderItemBahanBaku::create(['po_id' => $purchaseOrder->id, 'bahan_baku_id' => $item['item_id'], 'jumlah' => $item['jumlah'], 'harga_satuan' => $item['harga_satuan'], 'status_id' => $diajukanStatus?->id]);
                    } else {
                        PurchaseOrderItemMesin::create(['po_id' => $purchaseOrder->id, 'mesin_id' => $item['item_id'], 'jumlah' => (int) $item['jumlah'], 'harga_satuan' => $item['harga_satuan'], 'status_id' => $diajukanStatus?->id]);
                    }
                }
                $purchaseOrder->update(['total_nilai' => $totalNilai]);
            }
        });
        return response()->json(['message' => 'PO berhasil diperbarui.', 'data' => $this->formatPO($purchaseOrder->fresh()->load(['supplier.user', 'status', 'itemBahanBakus.bahanBaku', 'itemMesins.mesin']))]);
    }

    public function destroy(PurchaseOrder $purchaseOrder): JsonResponse
    {
        if ($purchaseOrder->status?->kode !== 'diajukan') return response()->json(['message' => 'Hanya PO diajukan yang bisa dihapus.'], 422);
        DB::transaction(function () use ($purchaseOrder) { $purchaseOrder->itemBahanBakus()->delete(); $purchaseOrder->itemMesins()->delete(); $purchaseOrder->delete(); });
        return response()->json(['message' => 'PO berhasil dihapus.']);
    }

    public function kirim(Request $request, $purchaseOrder): JsonResponse
    {
        $po = PurchaseOrder::find($purchaseOrder);
        if (! $po) return response()->json(['message' => 'PO tidak ditemukan.'], 404);
        if (! $po->status_id) {
            $diajukan = Status::where('konteks', 'purchase_order')->where('kode', 'diajukan')->first();
            if ($diajukan) { $po->update(['status_id' => $diajukan->id]); $po = $po->fresh(); }
        }
        $statusKode = Status::where('id', $po->status_id)->value('kode');
        if ($statusKode !== 'diajukan') return response()->json(['message' => 'Hanya PO diajukan yang bisa dikirim.'], 422);
        $hasItems = $po->itemBahanBakus()->count() > 0 || $po->itemMesins()->count() > 0;
        if (! $hasItems) return response()->json(['message' => 'PO harus memiliki minimal satu item.'], 422);
        $dikirimStatus = Status::where('konteks', 'purchase_order')->where('kode', 'dikirim')->first();
        $po->update(['status_id' => $dikirimStatus?->id]);
        return response()->json(['message' => 'PO berhasil dikirim ke supplier.', 'data' => $this->formatPO($po->fresh()->load(['supplier.user', 'status', 'itemBahanBakus.bahanBaku', 'itemMesins.mesin']))]);
    }

    public function validateItem(Request $request, PurchaseOrder $purchaseOrder, int $itemId): JsonResponse
    {
        if ($purchaseOrder->status?->kode !== 'dikirim') return response()->json(['message' => 'PO harus berstatus dikirim.'], 422);
        $request->validate([
            'status_kode' => 'required|in:disetujui,disetujui_sebagian,ditolak',
            'qty_disetujui' => 'required|numeric|min:0',
            'alasan' => 'required_if:status_kode,ditolak,disetujui_sebagian|nullable|string',
        ]);

        $item = $purchaseOrder->jenis_po === 'bahan_baku'
            ? PurchaseOrderItemBahanBaku::where('po_id', $purchaseOrder->id)->where('id', $itemId)->first()
            : PurchaseOrderItemMesin::where('po_id', $purchaseOrder->id)->where('id', $itemId)->first();
        if (! $item) return response()->json(['message' => 'Item tidak ditemukan.'], 404);

        $itemStatus = Status::where('konteks', 'purchase_order_item')->where('kode', $request->status_kode)->first();
        $item->update(['qty_disetujui' => $request->qty_disetujui, 'status_id' => $itemStatus?->id, 'alasan' => $request->alasan]);

        // Recalculate PO status from all items
        $itemStatusIds = collect();
        $itemStatusIds->push(...$purchaseOrder->itemBahanBakus()->pluck('status_id')->toArray());
        $itemStatusIds->push(...$purchaseOrder->itemMesins()->pluck('status_id')->toArray());
        $itemStatusIds = $itemStatusIds->filter()->unique();

        if ($itemStatusIds->isNotEmpty()) {
            $statusKodes = Status::whereIn('id', $itemStatusIds)->pluck('kode');
            if ($statusKodes->every(fn ($k) => $k === 'disetujui')) {
                $poStatus = Status::where('konteks', 'purchase_order')->where('kode', 'disetujui')->first();
            } elseif ($statusKodes->every(fn ($k) => $k === 'ditolak')) {
                $poStatus = Status::where('konteks', 'purchase_order')->where('kode', 'ditolak')->first();
            } else {
                $poStatus = Status::where('konteks', 'purchase_order')->where('kode', 'disetujui_sebagian')->first();
            }
            $purchaseOrder->update(['status_id' => $poStatus?->id]);
        }

        return response()->json(['message' => 'Item berhasil divalidasi.', 'data' => $this->formatPO($purchaseOrder->fresh()->load(['supplier.user', 'status', 'itemBahanBakus.bahanBaku', 'itemMesins.mesin']))]);
    }

    public function validatePo(Request $request, PurchaseOrder $purchaseOrder): JsonResponse
    {
        if (! $purchaseOrder->isDikirim()) {
            return response()->json(['message' => 'PO harus berstatus dikirim untuk divalidasi.'], 422);
        }
        $request->validate([
            'status_validasi' => 'required|in:disetujui,ditolak',
        ]);
        $purchaseOrder->update(['status_validasi' => $request->status_validasi]);
        return response()->json(['message' => 'PO berhasil divalidasi.']);
    }

    private function formatPO($po): array
    {
        return [
            'id' => $po->id, 'nomor_po' => $po->nomor_po,
            'supplier' => $po->supplier ? ['id' => $po->supplier->id, 'nama' => $po->supplier->user?->nama] : null,
            'dibuat_oleh' => $po->dibuatOleh ? ['id' => $po->dibuatOleh->id, 'nama' => $po->dibuatOleh->nama] : null,
            'jenis_po' => $po->jenis_po, 'total_nilai' => (float) $po->total_nilai,
            'status' => $po->status ? ['id' => $po->status->id, 'kode' => $po->status->kode, 'label' => $po->status->label] : null,
            'items_bahan_baku' => $po->itemBahanBakus->map(fn ($i) => ['id' => $i->id, 'nama' => $i->bahanBaku?->nama, 'jumlah' => (float) $i->jumlah, 'harga_satuan' => (float) $i->harga_satuan, 'qty_disetujui' => $i->qty_disetujui ? (float) $i->qty_disetujui : null, 'status' => $i->status ? $i->status->kode : null, 'alasan' => $i->alasan]),
            'items_mesin' => $po->itemMesins->map(fn ($i) => ['id' => $i->id, 'nama' => $i->mesin?->nama, 'jumlah' => $i->jumlah, 'harga_satuan' => (float) $i->harga_satuan, 'qty_disetujui' => $i->qty_disetujui, 'status' => $i->status ? $i->status->kode : null, 'alasan' => $i->alasan]),
            'created_at' => $po->created_at?->toDateTimeString(),
        ];
    }
}
