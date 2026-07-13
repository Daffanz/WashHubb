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

class DistribusiOutletController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = DistribusiOutlet::with(['permintaanStokOutlet.outlet', 'status', 'details.bahanBaku']);

        if ($outletId = $request->query('outlet_id')) {
            $query->whereHas('permintaanStokOutlet', fn ($q) => $q->where('outlet_id', $outletId));
        }

        $distribusis = $query->orderByDesc('created_at')->paginate(15);

        return response()->json([
            'data' => $distribusis->map(fn ($d) => $this->format($d)),
            'meta' => [
                'current_page' => $distribusis->currentPage(),
                'last_page' => $distribusis->lastPage(),
                'per_page' => $distribusis->perPage(),
                'total' => $distribusis->total(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'permintaan_stok_outlet_id' => 'required|exists:permintaan_stok_outlets,id',
            'tanggal_kirim' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.tipe_item' => 'required|in:bahan_baku,mesin',
            'items.*.bahan_baku_id' => 'nullable|exists:bahan_bakus,id',
            'items.*.mesin_id' => 'nullable|exists:mesins,id',
            'items.*.jumlah_kirim' => 'required|numeric|min:0.0001',
        ]);

        // Validate that the correct ID is provided based on tipe_item
        foreach ($request->items as $index => $item) {
            if ($item['tipe_item'] === 'bahan_baku' && empty($item['bahan_baku_id'])) {
                return response()->json(['message' => "Item {$index}: bahan_baku_id wajib diisi untuk tipe bahan_baku."], 422);
            }
            if ($item['tipe_item'] === 'mesin' && empty($item['mesin_id'])) {
                return response()->json(['message' => "Item {$index}: mesin_id wajib diisi untuk tipe mesin."], 422);
            }
        }

        $distribusi = DB::transaction(function () use ($request) {
            $dikirim = Status::where('konteks', 'distribusi_outlet')->where('kode', 'dikirim')->first();

            $distribusi = DistribusiOutlet::create([
                'permintaan_stok_outlet_id' => $request->permintaan_stok_outlet_id,
                'tanggal_kirim' => $request->tanggal_kirim,
                'status_id' => $dikirim?->id,
            ]);

            foreach ($request->items as $item) {
                DistribusiOutletDetail::create([
                    'distribusi_outlet_id' => $distribusi->id,
                    'bahan_baku_id' => $item['tipe_item'] === 'bahan_baku' ? $item['bahan_baku_id'] : null,
                    'mesin_id' => $item['tipe_item'] === 'mesin' ? $item['mesin_id'] : null,
                    'tipe_item' => $item['tipe_item'],
                    'jumlah_kirim' => $item['jumlah_kirim'],
                ]);
            }

            return $distribusi;
        });

        return response()->json([
            'message' => 'Distribusi berhasil dibuat.',
            'data' => $this->format($distribusi->fresh()->load(['permintaanStokOutlet.outlet', 'status', 'details.bahanBaku', 'details.mesin'])),
        ], 201);
    }

    public function show(DistribusiOutlet $distribusi): JsonResponse
    {
        $distribusi->load(['permintaanStokOutlet.outlet', 'status', 'details.bahanBaku', 'details.mesin', 'penerimaanOutlets.details']);
        return response()->json(['data' => $this->format($distribusi)]);
    }

    /**
     * Manajer Outlet konfirmasi penerimaan
     */
    public function terima(Request $request, DistribusiOutlet $distribusi): JsonResponse
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.distribusi_outlet_detail_id' => 'required|exists:distribusi_outlet_details,id',
            'items.*.qty_diterima' => 'required|numeric|min:0.0001',
        ]);

        DB::transaction(function () use ($request, $distribusi) {
            $outletId = $distribusi->permintaanStokOutlet->outlet_id;

            $penerimaan = PenerimaanOutlet::create([
                'distribusi_outlet_id' => $distribusi->id,
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

        return response()->json(['message' => 'Penerimaan berhasil dicatat. Stok outlet bertambah, stok pusat berkurang.']);
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

    private function format($d): array
    {
        return [
            'id' => $d->id,
            'permintaan' => $d->permintaanStokOutlet ? [
                'id' => $d->permintaanStokOutlet->id,
                'outlet' => $d->permintaanStokOutlet->outlet?->nama,
            ] : null,
            'tanggal_kirim' => $d->tanggal_kirim?->format('Y-m-d'),
            'status' => $d->status ? ['id' => $d->status->id, 'kode' => $d->status->kode, 'label' => $d->status->label] : null,
            'details' => $d->details->map(fn ($item) => [
                'id' => $item->id,
                'tipe_item' => $item->tipe_item,
                'bahan_baku' => $item->bahanBaku ? ['id' => $item->bahanBaku->id, 'nama' => $item->bahanBaku->nama] : null,
                'mesin' => $item->mesin ? ['id' => $item->mesin->id, 'nama' => $item->mesin->nama] : null,
                'jumlah_kirim' => (float) $item->jumlah_kirim,
            ]),
            'created_at' => $d->created_at?->toDateTimeString(),
        ];
    }
}
