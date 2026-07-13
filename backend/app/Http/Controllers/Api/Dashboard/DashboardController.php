<?php
namespace App\Http\Controllers\Api\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\OrderCucian;
use App\Models\Loyalti;
use App\Models\DetailMesin;
use App\Models\JadwalServiceMesin;
use App\Models\StokOutletBahanBaku;
use App\Models\StokPusatBahanBaku;
use App\Models\StokPusatMesin;
use App\Models\PurchaseOrder;
use App\Models\PermintaanStokOutlet;
use App\Models\Franchise;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * 7.1 Franchisor — ringkasan seluruh outlet
     */
    public function franchisor(Request $request): JsonResponse
    {
        $outlets = \App\Models\Outlet::with(['franchise.user', 'status'])->get();

        $data = $outlets->map(function ($outlet) {
            $omset = OrderCucian::where('outlet_id', $outlet->id)
                ->whereHas('status', fn ($q) => $q->where('kode', 'selesai'))
                ->sum('total_harga');

            $orderAktif = OrderCucian::where('outlet_id', $outlet->id)
                ->whereHas('status', fn ($q) => $q->where('kode', 'diproses'))
                ->count();

            $mesinBermasalah = DetailMesin::where('outlet_id', $outlet->id)
                ->whereHas('status', fn ($q) => $q->whereIn('kode', ['maintenance', 'nonaktif']))
                ->count();

            $loyaltiAktif = Loyalti::where('outlet_id', $outlet->id)
                ->whereHas('status', fn ($q) => $q->whereNotIn('kode', ['selesai', 'tidak_memenuhi_target']))
                ->first();

            return [
                'outlet' => ['id' => $outlet->id, 'nama' => $outlet->nama, 'kode' => $outlet->kode_outlet],
                'franchise' => $outlet->franchise?->user?->nama,
                'status' => $outlet->status?->kode,
                'omset' => (float) $omset,
                'order_aktif' => $orderAktif,
                'mesin_bermasalah' => $mesinBermasalah,
                'loyalti' => $loyaltiAktif ? [
                    'status' => $loyaltiAktif->status?->kode,
                    'memenuhi_target' => $loyaltiAktif->memenuhi_target,
                    'periode' => $loyaltiAktif->periode,
                ] : null,
            ];
        });

        return response()->json(['data' => $data]);
    }

    /**
     * 7.2 Tim Pengadaan — ringkasan procurement + stok pusat
     */
    public function pengadaan(): JsonResponse
    {
        $poMenunggu = PurchaseOrder::whereHas('status', fn ($q) => $q->where('kode', 'diajukan'))->count();
        $permintaanMenunggu = PermintaanStokOutlet::whereHas('status', fn ($q) => $q->where('kode', 'diajukan'))->count();

        $stokPusatBahan = StokPusatBahanBaku::with('bahanBaku')->get()->map(fn ($s) => [
            'item' => $s->bahanBaku?->nama,
            'stok_saat_ini' => (float) $s->stok_saat_ini,
            'kritis' => $s->stok_saat_ini < 10, // threshold configurable
        ]);

        $stokPusatMesin = StokPusatMesin::with('mesin')->get()->map(fn ($s) => [
            'item' => $s->mesin?->nama,
            'stok_saat_ini' => (int) $s->stok_saat_ini,
            'kritis' => $s->stok_saat_ini < 2,
        ]);

        return response()->json([
            'data' => [
                'po_menunggu' => $poMenunggu,
                'permintaan_outlet_menunggu' => $permintaanMenunggu,
                'stok_pusat_bahan_baku' => $stokPusatBahan,
                'stok_pusat_mesin' => $stokPusatMesin,
            ],
        ]);
    }

    /**
     * 7.3 Supplier — ringkasan PO + stok supplier
     */
    public function supplier(Request $request): JsonResponse
    {
        $supplier = $request->user()->suppliers()->first();
        if (!$supplier) {
            return response()->json(['data' => null]);
        }

        $poAktif = PurchaseOrder::where('supplier_id', $supplier->id)
            ->whereHas('status', fn ($q) => $q->whereNotIn('kode', ['selesai']))
            ->count();

        $stokSupplier = \App\Models\StokSupplierBahanBaku::where('supplier_id', $supplier->id)
            ->with('bahanBaku')->get()->map(fn ($s) => [
                'item' => $s->bahanBaku?->nama,
                'stok_saat_ini' => (float) $s->stok_saat_ini,
            ]);

        return response()->json([
            'data' => [
                'po_aktif' => $poAktif,
                'stok_supplier' => $stokSupplier,
            ],
        ]);
    }

    /**
     * 7.4 Franchisee — omzet outlet + loyalti
     */
    public function franchisee(Request $request): JsonResponse
    {
        $franchise = Franchise::where('user_id', $request->user()->id)->first();
        if (!$franchise) {
            return response()->json(['data' => null]);
        }

        $outlets = \App\Models\Outlet::where('franchise_id', $franchise->id)->get();

        $data = $outlets->map(function ($outlet) {
            $omset = OrderCucian::where('outlet_id', $outlet->id)
                ->whereHas('status', fn ($q) => $q->where('kode', 'selesai'))
                ->sum('total_harga');

            $loyalti = Loyalti::where('outlet_id', $outlet->id)
                ->with(['status', 'pencairans.status'])
                ->latest()->first();

            return [
                'outlet' => ['id' => $outlet->id, 'nama' => $outlet->nama],
                'omset' => (float) $omset,
                'loyalti' => $loyalti ? [
                    'periode' => $loyalti->periode,
                    'jumlah_bonus' => $loyalti->jumlah_bonus !== null ? (float) $loyalti->jumlah_bonus : null,
                    'status' => $loyalti->status?->kode,
                    'menunggu_konfirmasi' => $loyalti->status?->kode === 'diproses_pencairan',
                ] : null,
            ];
        });

        return response()->json(['data' => $data]);
    }

    /**
     * 7.5 Manajer Outlet — order, stok, mesin, service
     */
    public function manajerOutlet(Request $request): JsonResponse
    {
        $outletId = $request->user()->outlets()->first()?->id;
        if (!$outletId) {
            return response()->json(['data' => null]);
        }

        $orderAktif = OrderCucian::where('outlet_id', $outletId)
            ->whereHas('status', fn ($q) => $q->where('kode', 'diproses'))
            ->count();

        $stokKritis = StokOutletBahanBaku::where('outlet_id', $outletId)
            ->whereColumn('stok_saat_ini', '<', 'stok_minimum')
            ->with('bahanBaku')
            ->get()->map(fn ($s) => [
                'item' => $s->bahanBaku?->nama,
                'stok_saat_ini' => (float) $s->stok_saat_ini,
                'stok_minimum' => (float) $s->stok_minimum,
            ]);

        $mesinBermasalah = DetailMesin::where('outlet_id', $outletId)
            ->whereHas('status', fn ($q) => $q->whereIn('kode', ['maintenance', 'nonaktif']))
            ->with(['mesin', 'status'])
            ->get()->map(fn ($m) => [
                'mesin' => $m->mesin?->nama,
                'nomor_seri' => $m->nomor_seri,
                'status' => $m->status?->kode,
            ]);

        $serviceMenunggu = JadwalServiceMesin::where('outlet_id', $outletId)
            ->whereHas('status', fn ($q) => $q->where('kode', 'menunggu_persetujuan'))
            ->count();

        return response()->json([
            'data' => [
                'order_aktif' => $orderAktif,
                'stok_kritis' => $stokKritis,
                'mesin_bermasalah' => $mesinBermasalah,
                'service_menunggu_persetujuan' => $serviceMenunggu,
            ],
        ]);
    }
}
