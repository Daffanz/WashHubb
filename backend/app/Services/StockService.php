<?php

namespace App\Services;

use App\Models\MutasiStok;
use App\Models\PenerimaanBarang;
use App\Models\PurchaseOrderItem;
use App\Models\StokPusatBahanBaku;
use App\Models\StokPusatMesin;
use Illuminate\Support\Facades\DB;

class StockService
{
    public function updateFromReceipt(PenerimaanBarang $penerimaan): void
    {
        $distribusi = $penerimaan->distribusiBarang;
        $po         = $distribusi->purchaseOrder;

        DB::transaction(function () use ($po, $penerimaan) {
            $items = $po->items()->with('item')->get();

            foreach ($items as $item) {
                if ($po->jenis_po === 'bahan_baku' && $item->item_type === \App\Models\BahanBaku::class) {
                    $this->addStockBahanBaku($item->item_id, $item->jumlah, 'Masuk dari penerimaan PO: ' . $po->nomor_po);
                } elseif ($po->jenis_po === 'mesin' && $item->item_type === \App\Models\Mesin::class) {
                    $this->addStockMesin($item->item_id, (int) $item->jumlah, 'Masuk dari penerimaan PO: ' . $po->nomor_po);
                }
            }
        });
    }

    public function adjustStock(
        string $stokType,
        int    $stokId,
        float  $jumlah,
        string $jenisMutasi,
        string $keterangan = null,
    ): MutasiStok {
        return DB::transaction(function () use ($stokType, $stokId, $jumlah, $jenisMutasi, $keterangan) {
            // Lock the stock row for update
            if ($stokType === StokPusatBahanBaku::class) {
                $stok = StokPusatBahanBaku::lockForUpdate()->findOrFail($stokId);
            } elseif ($stokType === StokPusatMesin::class) {
                $stok = StokPusatMesin::lockForUpdate()->findOrFail($stokId);
            } else {
                throw new \InvalidArgumentException('Tipe stok tidak valid.');
            }

            $currentStock = (float) $stok->stok_saat_ini;

            if ($jenisMutasi === 'keluar' && $currentStock < $jumlah) {
                throw new \InvalidArgumentException(
                    "Stok tidak cukup. Stok saat ini: {$currentStock}, diminta: {$jumlah}"
                );
            }

            // Update stock balance
            $newBalance = match ($jenisMutasi) {
                'masuk'       => $currentStock + $jumlah,
                'keluar'      => $currentStock - $jumlah,
                'penyesuaian' => $jumlah, // Direct set for adjustments
                default       => $currentStock,
            };

            if ($newBalance < 0) {
                throw new \InvalidArgumentException('Stok tidak boleh negatif setelah penyesuaian.');
            }

            $stok->update(['stok_saat_ini' => $newBalance]);

            // Create mutation record
            return MutasiStok::create([
                'stok_type'    => $stokType,
                'stok_id'      => $stokId,
                'jenis_mutasi' => $jenisMutasi,
                'jumlah'       => $jumlah,
                'tanggal'      => now(),
                'keterangan'   => $keterangan,
            ]);
        });
    }

    private function addStockBahanBaku(int $bahanBakuId, float $jumlah, string $keterangan): void
    {
        $stok = StokPusatBahanBaku::lockForUpdate()
            ->firstOrCreate(
                ['bahan_baku_id' => $bahanBakuId],
                ['stok_saat_ini' => 0],
            );

        $stok->increment('stok_saat_ini', $jumlah);

        MutasiStok::create([
            'stok_type'    => StokPusatBahanBaku::class,
            'stok_id'      => $stok->id,
            'jenis_mutasi' => 'masuk',
            'jumlah'       => $jumlah,
            'tanggal'      => now(),
            'keterangan'   => $keterangan,
        ]);
    }

    private function addStockMesin(int $mesinId, int $jumlah, string $keterangan): void
    {
        $stok = StokPusatMesin::lockForUpdate()
            ->firstOrCreate(
                ['mesin_id' => $mesinId],
                ['stok_saat_ini' => 0],
            );

        $stok->increment('stok_saat_ini', $jumlah);

        MutasiStok::create([
            'stok_type'    => StokPusatMesin::class,
            'stok_id'      => $stok->id,
            'jenis_mutasi' => 'masuk',
            'jumlah'       => $jumlah,
            'tanggal'      => now(),
            'keterangan'   => $keterangan,
        ]);
    }
}
