<?php

namespace App\Services;

use App\Models\DistribusiBarang;
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
    public static function decreaseSupplierStock(DistribusiBarang $distribusi): void
    {
        $supplier = $distribusi->po->supplier;

        foreach ($distribusi->detailBahanBakus as $detail) {
            if (! $detail->poItem) continue;
            $stok = StokSupplierBahanBaku::lockForUpdate()
                ->where('supplier_id', $supplier->id)
                ->where('bahan_baku_id', $detail->poItem->bahan_baku_id)->first();
            if ($stok) {
                $stok->decrement('stok_saat_ini', $detail->jumlah_kirim);
                MutasiStokSupplierBahanBaku::create([
                    'stok_supplier_bahan_baku_id' => $stok->id, 'jenis_mutasi' => 'keluar',
                    'jumlah' => $detail->jumlah_kirim, 'tanggal' => now(),
                ]);
            }
        }

        foreach ($distribusi->detailMesins as $detail) {
            if (! $detail->poItem) continue;
            $stok = StokSupplierMesin::lockForUpdate()
                ->where('supplier_id', $supplier->id)
                ->where('mesin_id', $detail->poItem->mesin_id)->first();
            if ($stok) {
                $stok->decrement('stok_saat_ini', $detail->jumlah_kirim);
                MutasiStokSupplierMesin::create([
                    'stok_supplier_mesin_id' => $stok->id, 'jenis_mutasi' => 'keluar',
                    'jumlah' => $detail->jumlah_kirim, 'tanggal' => now(),
                ]);
            }
        }
    }

    public function updateFromReceipt(PenerimaanBarang $penerimaan): void
    {
        $po = $penerimaan->distribusiBarang->po;
        DB::transaction(function () use ($penerimaan, $po) {
            foreach ($penerimaan->detailBahanBakus as $detail) {
                if ($detail->kondisi === 'baik' && $detail->poItem) {
                    $this->addStockBahanBaku($detail->poItem->bahan_baku_id, (float) $detail->qty_diterima, $po->nomor_po, $detail->id);
                }
            }
            foreach ($penerimaan->detailMesins as $detail) {
                if ($detail->kondisi === 'baik' && $detail->poItem) {
                    $this->addStockMesin($detail->poItem->mesin_id, $detail->qty_diterima, $po->nomor_po, $detail->id);
                }
            }
        });
    }

    public function increaseStockFromRetur($returDetail): void
    {
        DB::transaction(function () use ($returDetail) {
            $poItem = $returDetail->penerimaanDetail->distribusiDetail->poItem;
            if (! $poItem) return;
            if ($returDetail instanceof \App\Models\ReturDetailBahanBaku && $poItem->bahan_baku_id) {
                $this->addStockBahanBaku($poItem->bahan_baku_id, (float) $returDetail->qty_pengganti, 'Retur pengganti', $returDetail->id);
            } elseif ($returDetail instanceof \App\Models\ReturDetailMesin && $poItem->mesin_id) {
                $this->addStockMesin($poItem->mesin_id, (int) $returDetail->qty_pengganti, 'Retur pengganti', $returDetail->id);
            }
        });
    }

    public function adjustStock(string $stokType, int $stokId, float $jumlah, string $jenisMutasi): void
    {
        DB::transaction(function () use ($stokType, $stokId, $jumlah, $jenisMutasi) {
            if ($stokType === 'bahan') {
                $stok = StokPusatBahanBaku::lockForUpdate()->findOrFail($stokId);
                $current = (float) $stok->stok_saat_ini;
                if ($jenisMutasi === 'keluar' && $current < $jumlah) {
                    throw new \InvalidArgumentException("Stok tidak cukup. Saat ini: {$current}");
                }
                $newBalance = match ($jenisMutasi) { 'masuk' => $current + $jumlah, 'keluar' => $current - $jumlah, default => $jumlah };
                $stok->update(['stok_saat_ini' => $newBalance]);
                if ($jenisMutasi === 'masuk') $stok->increment('stok_masuk', $jumlah);
                if ($jenisMutasi === 'keluar') $stok->increment('stok_keluar', $jumlah);
                MutasiStokPusatBahanBaku::create(['stok_pusat_bahan_baku_id' => $stok->id, 'jenis_mutasi' => $jenisMutasi, 'jumlah' => $jumlah, 'tanggal' => now()]);
            } else {
                $stok = StokPusatMesin::lockForUpdate()->findOrFail($stokId);
                $jumlahInt = (int) $jumlah;
                $current = (int) $stok->stok_saat_ini;
                if ($jenisMutasi === 'keluar' && $current < $jumlahInt) {
                    throw new \InvalidArgumentException("Stok tidak cukup. Saat ini: {$current}");
                }
                $newBalance = match ($jenisMutasi) { 'masuk' => $current + $jumlahInt, 'keluar' => $current - $jumlahInt, default => $jumlahInt };
                $stok->update(['stok_saat_ini' => $newBalance]);
                if ($jenisMutasi === 'masuk') $stok->increment('stok_masuk', $jumlahInt);
                if ($jenisMutasi === 'keluar') $stok->increment('stok_keluar', $jumlahInt);
                MutasiStokPusatMesin::create(['stok_pusat_mesin_id' => $stok->id, 'jenis_mutasi' => $jenisMutasi, 'jumlah' => $jumlahInt, 'tanggal' => now()]);
            }
        });
    }

    private function addStockBahanBaku(int $bahanBakuId, float $jumlah, string $nomorPo, int $penerimaanDetailId): void
    {
        $stok = StokPusatBahanBaku::lockForUpdate()->firstOrCreate(['bahan_baku_id' => $bahanBakuId], ['stok_saat_ini' => 0, 'stok_masuk' => 0, 'stok_keluar' => 0]);
        $stok->increment('stok_saat_ini', $jumlah);
        $stok->increment('stok_masuk', $jumlah);
        MutasiStokPusatBahanBaku::create([
            'stok_pusat_bahan_baku_id' => $stok->id, 'jenis_mutasi' => 'masuk', 'jumlah' => $jumlah,
            'tanggal' => now(), 'penerimaan_detail_id' => $penerimaanDetailId,
        ]);
    }

    private function addStockMesin(int $mesinId, int $jumlah, string $nomorPo, int $penerimaanDetailId): void
    {
        $stok = StokPusatMesin::lockForUpdate()->firstOrCreate(['mesin_id' => $mesinId], ['stok_saat_ini' => 0, 'stok_masuk' => 0, 'stok_keluar' => 0]);
        $stok->increment('stok_saat_ini', $jumlah);
        $stok->increment('stok_masuk', $jumlah);
        MutasiStokPusatMesin::create([
            'stok_pusat_mesin_id' => $stok->id, 'jenis_mutasi' => 'masuk', 'jumlah' => $jumlah,
            'tanggal' => now(), 'penerimaan_detail_id' => $penerimaanDetailId,
        ]);
    }
}
