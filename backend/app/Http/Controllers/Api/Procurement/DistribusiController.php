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
    public function index(Request $request): JsonResponse
    {
        $query = DistribusiBarang::with(['po.supplier.user', 'status', 'detailBahanBakus.poItem.bahanBaku', 'detailMesins.poItem.mesin']);

        if ($request->user()->hasRole('supplier')) {
            $supplier = $request->user()->suppliers()->first();
            if ($supplier) {
                $query->whereHas('po', fn ($q) => $q->where('supplier_id', $supplier->id));
            }
        }

        if ($request->filled('status')) {
            $query->whereHas('status', fn ($q) => $q->where('kode', $request->status));
        }

        $distribusis = $query->orderByDesc('created_at')->paginate(15);
        return response()->json([
            'data' => $distribusis->map(fn ($d) => $this->formatDistribusi($d)),
            'meta' => ['current_page' => $distribusis->currentPage(), 'last_page' => $distribusis->lastPage(), 'per_page' => $distribusis->perPage(), 'total' => $distribusis->total()],
        ]);
    }

    /**
     * UC-35: Supplier buat distribusi manual untuk item yang disetujui
     * Status: dikirim atau dikirim_sebagian
     * Validasi: jumlah_kirim ≤ qty_disetujui
     * Blokir jika ada distribusi sebelumnya yang belum dikonfirmasi
     */
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

        $po = \App\Models\PurchaseOrder::findOrFail($request->po_id);
        if (! in_array($po->status?->kode, ['disetujui', 'disetujui_sebagian'])) {
            return response()->json(['message' => 'PO harus berstatus disetujui atau disetujui_sebagian.'], 422);
        }

        // Blokir jika ada distribusi yang belum dikonfirmasi diterima
        $pendingDist = DistribusiBarang::where('po_id', $request->po_id)
            ->whereHas('status', fn ($q) => $q->whereIn('kode', ['dikirim', 'dikirim_sebagian']))
            ->count();
        if ($pendingDist > 0) {
            return response()->json(['message' => 'Masih ada distribusi sebelumnya yang belum dikonfirmasi diterima.'], 422);
        }

        // Validasi jumlah_kirim ≤ qty_disetujui
        if ($request->filled('items_bahan_baku')) {
            foreach ($request->items_bahan_baku as $item) {
                $poItem = \App\Models\PurchaseOrderItemBahanBaku::find($item['po_item_id']);
                if ($poItem && $item['jumlah_kirim'] > $poItem->qty_disetujui) {
                    return response()->json(['message' => "Jumlah kirim ({$item['jumlah_kirim']}) melebihi jumlah disetujui ({$poItem->qty_disetujui})."], 422);
                }
                // Cek stok supplier
                $stokSupplier = \App\Models\StokSupplierBahanBaku::where('supplier_id', $po->supplier_id)
                    ->where('bahan_baku_id', $poItem->bahan_baku_id)->first();
                if (! $stokSupplier || $stokSupplier->stok_saat_ini < $item['jumlah_kirim']) {
                    $nama = $poItem->bahanBaku?->nama;
                    $stokAda = $stokSupplier ? $stokSupplier->stok_saat_ini : 0;
                    return response()->json(['message' => "Stok {$nama} tidak cukup. Stok saat ini: {$stokAda}, diperlukan: {$item['jumlah_kirim']}. Tambah stok atau perbarui jumlah kirim."], 422);
                }
            }
        }
        if ($request->filled('items_mesin')) {
            foreach ($request->items_mesin as $item) {
                $poItem = \App\Models\PurchaseOrderItemMesin::find($item['po_item_id']);
                if ($poItem && $item['jumlah_kirim'] > $poItem->qty_disetujui) {
                    return response()->json(['message' => "Jumlah kirim ({$item['jumlah_kirim']}) melebihi jumlah disetujui ({$poItem->qty_disetujui})."], 422);
                }
                // Cek stok supplier
                $stokSupplier = \App\Models\StokSupplierMesin::where('supplier_id', $po->supplier_id)
                    ->where('mesin_id', $poItem->mesin_id)->first();
                if (! $stokSupplier || $stokSupplier->stok_saat_ini < $item['jumlah_kirim']) {
                    $nama = $poItem->mesin?->nama;
                    $stokAda = $stokSupplier ? $stokSupplier->stok_saat_ini : 0;
                    return response()->json(['message' => "Stok {$nama} tidak cukup. Stok saat ini: {$stokAda}, diperlukan: {$item['jumlah_kirim']}. Tambah stok atau perbarui jumlah kirim."], 422);
                }
            }
        }

        $distribusi = DB::transaction(function () use ($request, $po) {
            // Tentukan status distribusi
            $allFull = true;
            foreach ($request->items_bahan_baku ?? [] as $item) {
                $poItem = \App\Models\PurchaseOrderItemBahanBaku::find($item['po_item_id']);
                if ($poItem && $item['jumlah_kirim'] < $poItem->qty_disetujui) {
                    $allFull = false;
                    break;
                }
            }
            if ($allFull) {
                foreach ($request->items_mesin ?? [] as $item) {
                    $poItem = \App\Models\PurchaseOrderItemMesin::find($item['po_item_id']);
                    if ($poItem && $item['jumlah_kirim'] < $poItem->qty_disetujui) {
                        $allFull = false;
                        break;
                    }
                }
            }

            $kodeStatus = $allFull ? 'dikirim' : 'dikirim_sebagian';
            $distStatus = Status::where('konteks', 'distribusi_barang')->where('kode', $kodeStatus)->first();

            $distribusi = DistribusiBarang::create([
                'po_id' => $po->id,
                'tanggal_kirim' => $request->tanggal_kirim,
                'status_id' => $distStatus?->id,
            ]);

            foreach ($request->items_bahan_baku ?? [] as $item) {
                DistDetailBahanBaku::create([
                    'distribusi_barang_id' => $distribusi->id,
                    'po_item_id' => $item['po_item_id'],
                    'jumlah_kirim' => $item['jumlah_kirim'],
                ]);
            }
            foreach ($request->items_mesin ?? [] as $item) {
                DistDetailMesin::create([
                    'distribusi_barang_id' => $distribusi->id,
                    'po_item_id' => $item['po_item_id'],
                    'jumlah_kirim' => (int) $item['jumlah_kirim'],
                ]);
            }

            return $distribusi;
        });

        return response()->json(['message' => 'Distribusi berhasil dibuat.', 'data' => $this->formatDistribusi($distribusi->load(['po.supplier.user', 'status', 'detailBahanBakus.poItem', 'detailMesins.poItem']))], 201);
    }

    public function show(DistribusiBarang $distribusi): JsonResponse
    {
        $distribusi->load(['po.supplier.user', 'status', 'detailBahanBakus.poItem.bahanBaku', 'detailMesins.poItem.mesin', 'penerimaanBarangs']);
        return response()->json(['data' => $this->formatDistribusi($distribusi)]);
    }

    /**
     * Tim Pengadaan tandai distribusi diterima saat barang sampai
     */
    public function diterima(DistribusiBarang $distribusi): JsonResponse
    {
        $validStatuses = ['dikirim', 'dikirim_sebagian'];
        if (! in_array($distribusi->status?->kode, $validStatuses)) {
            return response()->json(['message' => 'Hanya distribusi dikirim/dikirim_sebagian yang bisa ditandai diterima.'], 422);
        }

        DB::transaction(function () use ($distribusi) {
            $diterimaDist = Status::where('konteks', 'distribusi_barang')->where('kode', 'diterima')->first();
            $distribusi->update(['status_id' => $diterimaDist?->id]);
        });

        return response()->json(['message' => 'Distribusi ditandai diterima.']);
    }

    private function formatDistribusi($d): array
    {
        return [
            'id' => $d->id, 'nomor_distribusi' => $d->nomor_distribusi,
            'po' => $d->po ? ['id' => $d->po->id, 'nomor_po' => $d->po->nomor_po, 'jenis_po' => $d->po->jenis_po] : null,
            'tanggal_kirim' => $d->tanggal_kirim?->format('Y-m-d'),
            'status' => $d->status ? ['id' => $d->status->id, 'kode' => $d->status->kode, 'label' => $d->status->label] : null,
            'items_bahan_baku' => $d->detailBahanBakus->map(fn ($i) => ['id' => $i->id, 'po_item_id' => $i->po_item_id, 'nama' => $i->poItem?->bahanBaku?->nama, 'jumlah_kirim' => (float) $i->jumlah_kirim, 'qty_disetujui' => (float) ($i->poItem?->qty_disetujui ?? 0), 'harga_satuan' => (float) ($i->poItem?->harga_satuan ?? 0)]),
            'items_mesin' => $d->detailMesins->map(fn ($i) => ['id' => $i->id, 'po_item_id' => $i->po_item_id, 'nama' => $i->poItem?->mesin?->nama, 'jumlah_kirim' => $i->jumlah_kirim, 'qty_disetujui' => (int) ($i->poItem?->qty_disetujui ?? 0), 'harga_satuan' => (float) ($i->poItem?->harga_satuan ?? 0)]),
            'created_at' => $d->created_at?->toDateTimeString(),
        ];
    }
}
