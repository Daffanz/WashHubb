<?php

namespace App\Http\Controllers\Api\Procurement;

use App\Http\Controllers\Controller;
use App\Http\Requests\Procurement\StorePurchaseOrderRequest;
use App\Http\Requests\Procurement\UpdatePurchaseOrderRequest;
use App\Http\Resources\Procurement\PurchaseOrderResource;
use App\Models\PurchaseOrder;
use App\Models\Status;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;

class PurchaseOrderController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = PurchaseOrder::with(['supplier', 'status', 'items']);

        // Supplier only sees their own POs
        if ($request->user()->hasRole('supplier')) {
            $query->forSupplier($request->user()->id);
        }

        return PurchaseOrderResource::collection($query->paginate(15));
    }

    public function store(StorePurchaseOrderRequest $request): JsonResponse
    {
        $po = DB::transaction(function () use ($request) {
            $draftStatus = Status::where('group', 'purchase_order')->where('name', 'draft')->first();

            $po = PurchaseOrder::create([
                'supplier_id' => $request->supplier_id,
                'jenis_po'    => $request->jenis_po,
                'status_id'   => $draftStatus?->id,
                'catatan'     => $request->catatan,
            ]);

            foreach ($request->items as $item) {
                $po->items()->create([
                    'item_type'   => $item['item_type'],
                    'item_id'     => $item['item_id'],
                    'jumlah'      => $item['jumlah'],
                    'harga_satuan' => $item['harga_satuan'],
                ]);
            }

            return $po;
        });

        return response()->json([
            'message' => 'Purchase Order berhasil dibuat.',
            'data'    => new PurchaseOrderResource($po->load(['supplier', 'status', 'items'])),
        ], 201);
    }

    public function show(PurchaseOrder $purchaseOrder): JsonResponse
    {
        return response()->json([
            'data' => new PurchaseOrderResource($purchaseOrder->load(['supplier', 'status', 'items'])),
        ]);
    }

    public function update(UpdatePurchaseOrderRequest $request, PurchaseOrder $purchaseOrder): JsonResponse
    {
        if (! $purchaseOrder->isDraft()) {
            return response()->json(['message' => 'Hanya PO draft yang bisa diedit.'], 422);
        }

        DB::transaction(function () use ($request, $purchaseOrder) {
            $purchaseOrder->update($request->only(['supplier_id', 'jenis_po', 'catatan']));

            if ($request->filled('items')) {
                $purchaseOrder->items()->delete();
                foreach ($request->items as $item) {
                    $purchaseOrder->items()->create([
                        'item_type'   => $item['item_type'],
                        'item_id'     => $item['item_id'],
                        'jumlah'      => $item['jumlah'],
                        'harga_satuan' => $item['harga_satuan'],
                    ]);
                }
            }
        });

        return response()->json([
            'message' => 'Purchase Order berhasil diperbarui.',
            'data'    => new PurchaseOrderResource($purchaseOrder->fresh()->load(['supplier', 'status', 'items'])),
        ]);
    }

    public function destroy(PurchaseOrder $purchaseOrder): JsonResponse
    {
        if (! $purchaseOrder->isDraft()) {
            return response()->json(['message' => 'Hanya PO draft yang bisa dihapus.'], 422);
        }

        $purchaseOrder->delete();

        return response()->json(['message' => 'Purchase Order berhasil dihapus.']);
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

        return response()->json([
            'message' => 'PO berhasil divalidasi.',
            'data'    => new PurchaseOrderResource($purchaseOrder->fresh()->load(['supplier', 'status', 'items'])),
        ]);
    }
}
