<?php

namespace App\Services;

use App\Models\PenerimaanBarang;
use App\Models\MutasiStokPusatBahanBaku;
use App\Models\MutasiStokPusatMesin;
use App\Models\MutasiStokSupplierBahanBaku;
use App\Models\MutasiStokSupplierMesin;
use App\Models\StokPusatBahanBaku;
use App\Models\StokPusatMesin;
use App\Models\StokSupplierBahanBaku;
use App\Models\StokSupplierMesin;
use Illuminate\Support\Facades\DB;

class StockService
{
    /**
     * Kurangi stok supplier untuk barang yang kondisi baik
     * Dipanggil saat receipt dibuat
     */
    public function decreaseSupplierStockFromReceipt(PenerimaanBarang $penerimaan): void
    {
        $po = $penerimaan->distribusiBarang->po;
        $supplier = $po->supplier;

        DB::transaction(function () use ($penerimaan, $po, $supplier) {
            foreach ($penerimaan->detailBahanBakus as $detail) {
                $bahanBakuId = $detail->distribusiDetail?->poItem?->bahan_baku_id;
                if (!$bahanBakuId) continue;

                $stokSupplier = StokSupplierBahanBaku::lockForUpdate()
                    ->where('supplier_id', $supplier->id)
                    ->where('bahan_baku_id', $bahanBakuId)->first();
                if ($stokSupplier) {
                    $stokSupplier->decrement('stok_saat_ini', $detail->qty_diterima);
                    MutasiStokSupplierBahanBaku::create([
                        'stok_supplier_bahan_baku_id' => $stokSupplier->id,
                        'jenis_mutasi' => 'keluar',
                        'jumlah' => $detail->qty_diterima,
                        'tanggal' => now(),
                    ]);
                }
            }
            foreach ($penerimaan->detailMesins as $detail) {
                $mesinId = $detail->distribusiDetail?->poItem?->mesin_id;
                if (!$mesinId) continue;

                $stokSupplier = StokSupplierMesin::lockForUpdate()
                    ->where('supplier_id', $supplier->id)
                    ->where('mesin_id', $mesinId)->first();
                if ($stokSupplier) {
                    $stokSupplier->decrement('stok_saat_ini', (int) $detail->qty_diterima);
                    MutasiStokSupplierMesin::create([
                        'stok_supplier_mesin_id' => $stokSupplier->id,
                        'jenis_mutasi' => 'keluar',
                        'jumlah' => (int) $detail->qty_diterima,
                        'tanggal' => now(),
                    ]);
                }
            }
        });
    }

    /**
     * Tambah stok perusahaan dari receipt yang sudah selesai.
     * FULL jumlah qty_diterima — semua item masuk stok, baik maupun cacat.
     * Yang cacat nanti dicatat di retur untuk diganti, tapi stok tetap full.
     */
    public function increaseCompanyStockFromReceipt(PenerimaanBarang $penerimaan): void
    {
        DB::transaction(function () use ($penerimaan) {
            foreach ($penerimaan->detailBahanBakus as $detail) {
                $bahanBakuId = $detail->distribusiDetail?->poItem?->bahan_baku_id;
                if (!$bahanBakuId) continue;

                $this->addStockBahanBaku($bahanBakuId, (float) $detail->qty_diterima, $detail->id);
            }
            foreach ($penerimaan->detailMesins as $detail) {
                $mesinId = $detail->distribusiDetail?->poItem?->mesin_id;
                if (!$mesinId) continue;

                $this->addStockMesin($mesinId, (int) $detail->qty_diterima, $detail->id);
            }
        });
    }

    private function addStockBahanBaku(int $bahanBakuId, float $jumlah, ?int $penerimaanDetailId): void
    {
        $stok = StokPusatBahanBaku::lockForUpdate()->firstOrCreate(
            ['bahan_baku_id' => $bahanBakuId],
            ['stok_saat_ini' => 0, 'stok_masuk' => 0, 'stok_keluar' => 0]
        );
        $stok->increment('stok_saat_ini', $jumlah);
        $stok->increment('stok_masuk', $jumlah);
        MutasiStokPusatBahanBaku::create([
            'stok_pusat_bahan_baku_id' => $stok->id,
            'jenis_mutasi' => 'masuk',
            'jumlah' => $jumlah,
            'tanggal' => now(),
            'penerimaan_detail_id' => $penerimaanDetailId,
        ]);
    }

    private function addStockMesin(int $mesinId, int $jumlah, ?int $penerimaanDetailId): void
    {
        $stok = StokPusatMesin::lockForUpdate()->firstOrCreate(
            ['mesin_id' => $mesinId],
            ['stok_saat_ini' => 0, 'stok_masuk' => 0, 'stok_keluar' => 0]
        );
        $stok->increment('stok_saat_ini', $jumlah);
        $stok->increment('stok_masuk', $jumlah);
        MutasiStokPusatMesin::create([
            'stok_pusat_mesin_id' => $stok->id,
            'jenis_mutasi' => 'masuk',
            'jumlah' => $jumlah,
            'tanggal' => now(),
            'penerimaan_detail_id' => $penerimaanDetailId,
        ]);
    }
}
