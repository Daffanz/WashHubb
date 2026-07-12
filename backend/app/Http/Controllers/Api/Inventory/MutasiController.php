<?php
namespace App\Http\Controllers\Api\Inventory;

use App\Http\Controllers\Controller;
use App\Models\MutasiStokPusatBahanBaku;
use App\Models\MutasiStokPusatMesin;
use App\Models\PenerimaanBarang;
use App\Models\StokPusatBahanBaku;
use App\Models\StokPusatMesin;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MutasiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $type = $request->query('type', 'bahan');

        if ($type === 'mesin') {
            $mutasis = MutasiStokPusatMesin::with([
                    'stokPusat.mesin',
                    'penerimaanDetail.penerimaanBarang.distribusiBarang.po',
                ])
                ->orderByDesc('tanggal')->paginate(15);
        } else {
            $mutasis = MutasiStokPusatBahanBaku::with([
                    'stokPusat.bahanBaku',
                    'penerimaanDetail.penerimaanBarang.distribusiBarang.po',
                ])
                ->orderByDesc('tanggal')->paginate(15);
        }

        return response()->json([
            'data' => $mutasis->map(fn ($m) => [
                'id' => $m->id,
                'jenis_mutasi' => $m->jenis_mutasi,
                'jumlah' => $m->jumlah,
                'item_nama' => $type === 'mesin'
                    ? ($m->stokPusat?->mesin?->nama ?? '-')
                    : ($m->stokPusat?->bahanBaku?->nama ?? '-'),
                'tanggal' => $m->tanggal?->toDateTimeString(),
                'penerimaan' => $m->penerimaanDetail?->penerimaanBarang ? [
                    'id' => $m->penerimaanDetail->penerimaanBarang->id,
                    'nomor_penerimaan' => $m->penerimaanDetail->penerimaanBarang->nomor_penerimaan,
                    'nomor_po' => $m->penerimaanDetail->penerimaanBarang->distribusiBarang?->po?->nomor_po,
                ] : null,
                'created_at' => $m->created_at?->toDateTimeString(),
            ]),
            'meta' => [
                'current_page' => $mutasis->currentPage(),
                'last_page' => $mutasis->lastPage(),
                'per_page' => $mutasis->perPage(),
                'total' => $mutasis->total(),
            ],
        ]);
    }

    /**
     * Mutasi masuk: dari receipt selesai
     * Full qty_diterima masuk stok — tidak peduli retur
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'stok_type' => 'required|in:bahan,mesin',
            'jenis_mutasi' => 'required|in:masuk,keluar',
            'referensi_penerimaan_id' => 'required_if:jenis_mutasi,masuk|nullable|exists:penerimaan_barangs,id',
            'stok_id' => 'required_if:jenis_mutasi,keluar|nullable|integer',
            'jumlah' => 'required_if:jenis_mutasi,keluar|nullable|numeric|min:0.0001',
        ]);

        if ($request->jenis_mutasi === 'masuk') {
            $this->handleMasuk($request);
            return response()->json(['message' => 'Mutasi masuk berhasil dicatat. Stok perusahaan bertambah.'], 201);
        }

        DB::transaction(fn () => $this->handleKeluar($request));
        return response()->json(['message' => 'Mutasi keluar berhasil dicatat.'], 201);
    }

    private function handleMasuk(Request $request): void
    {
        $penerimaan = PenerimaanBarang::with([
            'detailBahanBakus.distribusiDetail.poItem.bahanBaku',
            'detailMesins.distribusiDetail.poItem.mesin',
        ])->findOrFail($request->referensi_penerimaan_id);

        if ($penerimaan->status?->kode !== 'selesai') {
            throw new \InvalidArgumentException('Mutasi masuk hanya bisa dari penerimaan yang sudah selesai.');
        }

        $stockService = new \App\Services\StockService();
        $stockService->increaseCompanyStockFromReceipt($penerimaan);
    }

    private function handleKeluar(Request $request): void
    {
        if ($request->stok_type === 'mesin') {
            $stok = StokPusatMesin::lockForUpdate()->findOrFail($request->stok_id);
            $current = (int) $stok->stok_saat_ini;
            $jumlah = (int) $request->jumlah;
            if ($current < $jumlah) throw new \InvalidArgumentException("Stok tidak cukup. Saat ini: {$current}");
            $stok->update(['stok_saat_ini' => $current - $jumlah, 'stok_keluar' => $stok->stok_keluar + $jumlah]);
            MutasiStokPusatMesin::create(['stok_pusat_mesin_id' => $stok->id, 'jenis_mutasi' => 'keluar', 'jumlah' => $jumlah, 'tanggal' => now()]);
        } else {
            $stok = StokPusatBahanBaku::lockForUpdate()->findOrFail($request->stok_id);
            $current = (float) $stok->stok_saat_ini;
            $jumlah = (float) $request->jumlah;
            if ($current < $jumlah) throw new \InvalidArgumentException("Stok tidak cukup. Saat ini: {$current}");
            $stok->update(['stok_saat_ini' => $current - $jumlah, 'stok_keluar' => $stok->stok_keluar + $jumlah]);
            MutasiStokPusatBahanBaku::create(['stok_pusat_bahan_baku_id' => $stok->id, 'jenis_mutasi' => 'keluar', 'jumlah' => $jumlah, 'tanggal' => now()]);
        }
    }
}
