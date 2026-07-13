<?php
namespace App\Http\Controllers\Api\Operasional;

use App\Http\Controllers\Controller;
use App\Models\DistribusiOutlet;
use App\Models\DistribusiOutletDetail;
use App\Models\PenerimaanOutlet;
use App\Models\PenerimaanOutletDetail;
use App\Models\StokOutletBahanBaku;
use App\Models\StokOutletMesin;
use App\Models\MutasiStokOutletBahanBaku;
use App\Models\MutasiStokOutletMesin;
use App\Models\StokPusatBahanBaku;
use App\Models\MutasiStokPusatBahanBaku;
use App\Models\Status;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PenerimaanStokOutletController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = PenerimaanOutlet::with(['distribusiOutlet.permintaanStokOutlet.outlet', 'distribusiOutlet.details.bahanBaku', 'distribusiOutlet.details.mesin', 'user', 'details.distribusiOutletDetail.bahanBaku', 'details.distribusiOutletDetail.mesin']);

        if ($outletId = $request->query('outlet_id')) {
            $query->whereHas('distribusiOutlet.permintaanStokOutlet', fn ($q) => $q->where('outlet_id', $outletId));
        }

        $penerimaans = $query->orderByDesc('created_at')->paginate(15);

        return response()->json([
            'data' => $penerimaans->map(fn ($p) => $this->format($p)),
            'meta' => [
                'current_page' => $penerimaans->currentPage(),
                'last_page' => $penerimaans->lastPage(),
                'per_page' => $penerimaans->perPage(),
                'total' => $penerimaans->total(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'distribusi_outlet_id' => 'required|exists:distribusi_outlets,id',
            'items' => 'required|array|min:1',
            'items.*.distribusi_outlet_detail_id' => 'required|exists:distribusi_outlet_details,id',
            'items.*.qty_diterima' => 'required|numeric|min:0.0001',
        ]);

        $distribusi = DistribusiOutlet::findOrFail($request->distribusi_outlet_id);
        $outletId = $distribusi->permintaanStokOutlet->outlet_id;

        DB::transaction(function () use ($request, $distribusi, $outletId) {
            $penerimaan = PenerimaanOutlet::create([
                'distribusi_outlet_id' => $request->distribusi_outlet_id,
                'user_id' => $request->user()->id,
                'tanggal_terima' => now(),
            ]);

            foreach ($request->items as $item) {
                $distDetail = DistribusiOutletDetail::findOrFail($item['distribusi_outlet_detail_id']);

                $penerimaanDetail = PenerimaanOutletDetail::create([
                    'penerimaan_outlet_id' => $penerimaan->id,
                    'distribusi_outlet_detail_id' => $item['distribusi_outlet_detail_id'],
                    'mesin_id' => $distDetail->mesin_id,
                    'tipe_item' => $distDetail->tipe_item,
                    'qty_diterima' => $item['qty_diterima'],
                ]);

                if ($distDetail->tipe_item === 'mesin' && $distDetail->mesin_id) {
                    // Tambah stok outlet mesin
                    $stokOutlet = StokOutletMesin::lockForUpdate()->firstOrCreate(
                        ['outlet_id' => $outletId, 'mesin_id' => $distDetail->mesin_id],
                        ['stok_saat_ini' => 0, 'stok_minimum' => 0, 'stok_masuk' => 0, 'stok_keluar' => 0]
                    );
                    $stokOutlet->increment('stok_saat_ini', $item['qty_diterima']);
                    $stokOutlet->increment('stok_masuk', $item['qty_diterima']);

                    MutasiStokOutletMesin::create([
                        'stok_outlet_mesin_id' => $stokOutlet->id,
                        'jenis_mutasi' => 'masuk',
                        'jumlah' => $item['qty_diterima'],
                        'tanggal' => now(),
                        'penerimaan_outlet_detail_id' => $penerimaanDetail->id,
                    ]);
                } else {
                    // Tambah stok outlet bahan baku
                    $stokOutlet = StokOutletBahanBaku::lockForUpdate()->firstOrCreate(
                        ['outlet_id' => $outletId, 'bahan_baku_id' => $distDetail->bahan_baku_id],
                        ['stok_saat_ini' => 0, 'stok_minimum' => 0, 'stok_masuk' => 0, 'stok_keluar' => 0]
                    );
                    $stokOutlet->increment('stok_saat_ini', $item['qty_diterima']);
                    $stokOutlet->increment('stok_masuk', $item['qty_diterima']);

                    MutasiStokOutletBahanBaku::create([
                        'stok_outlet_bahan_baku_id' => $stokOutlet->id,
                        'jenis_mutasi' => 'masuk',
                        'jumlah' => $item['qty_diterima'],
                        'tanggal' => now(),
                        'penerimaan_outlet_detail_id' => $penerimaanDetail->id,
                    ]);

                    // Kurangi stok pusat bahan baku
                    $stokPusat = StokPusatBahanBaku::lockForUpdate()
                        ->where('bahan_baku_id', $distDetail->bahan_baku_id)->first();
                    if ($stokPusat) {
                        $stokPusat->decrement('stok_saat_ini', $item['qty_diterima']);
                        $stokPusat->increment('stok_keluar', $item['qty_diterima']);

                        MutasiStokPusatBahanBaku::create([
                            'stok_pusat_bahan_baku_id' => $stokPusat->id,
                            'jenis_mutasi' => 'keluar',
                            'jumlah' => $item['qty_diterima'],
                            'tanggal' => now(),
                            'penerimaan_outlet_detail_id' => $penerimaanDetail->id,
                        ]);
                    }
                }
            }

            // Update status distribusi
            $this->updateDistribusiStatus($distribusi);
        });

        return response()->json(['message' => 'Penerimaan berhasil dicatat. Stok outlet bertambah, stok pusat berkurang.'], 201);
    }

    public function show(PenerimaanOutlet $penerimaan): JsonResponse
    {
        $penerimaan->load(['distribusiOutlet.permintaanStokOutlet.outlet', 'distribusiOutlet.details.bahanBaku', 'distribusiOutlet.details.mesin', 'user', 'details.distribusiOutletDetail.bahanBaku', 'details.distribusiOutletDetail.mesin']);
        return response()->json(['data' => $this->format($penerimaan)]);
    }

    private function updateDistribusiStatus(DistribusiOutlet $distribusi): void
    {
        $distribusi->load('details.penerimaanOutlets');

        $allReceived = true;
        foreach ($distribusi->details as $detail) {
            $totalDiterima = $detail->penerimaanOutlets->sum('qty_diterima');
            if ($totalDiterima < $detail->jumlah_kirim) {
                $allReceived = false;
                break;
            }
        }

        $kode = $allReceived ? 'diterima' : 'dikirim_sebagian';
        $status = Status::where('konteks', 'distribusi_outlet')->where('kode', $kode)->first();
        $distribusi->update(['status_id' => $status?->id]);
    }

    private function format($p): array
    {
        return [
            'id' => $p->id,
            'distribusi' => $p->distribusiOutlet ? [
                'id' => $p->distribusiOutlet->id,
                'outlet' => $p->distribusiOutlet->permintaanStokOutlet?->outlet?->nama,
                'tanggal_kirim' => $p->distribusiOutlet->tanggal_kirim?->format('Y-m-d'),
            ] : null,
            'user' => $p->user?->nama,
            'tanggal_terima' => $p->tanggal_terima?->format('Y-m-d'),
            'details' => $p->details->map(fn ($d) => [
                'id' => $d->id,
                'tipe_item' => $d->tipe_item,
                'bahan_baku' => $d->distribusiOutletDetail?->bahanBaku ? ['id' => $d->distribusiOutletDetail->bahanBaku->id, 'nama' => $d->distribusiOutletDetail->bahanBaku->nama] : null,
                'mesin' => $d->distribusiOutletDetail?->mesin ? ['id' => $d->distribusiOutletDetail->mesin->id, 'nama' => $d->distribusiOutletDetail->mesin->nama] : null,
                'jumlah_kirim' => (float) ($d->distribusiOutletDetail?->jumlah_kirim ?? 0),
                'qty_diterima' => (float) $d->qty_diterima,
            ]),
            'created_at' => $p->created_at?->toDateTimeString(),
        ];
    }
}
