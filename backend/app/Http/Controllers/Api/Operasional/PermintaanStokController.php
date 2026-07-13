<?php
namespace App\Http\Controllers\Api\Operasional;

use App\Http\Controllers\Controller;
use App\Models\PermintaanStokOutlet;
use App\Models\PermintaanStokOutletDetail;
use App\Models\Status;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PermintaanStokController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $query = PermintaanStokOutlet::with(['outlet', 'user', 'status', 'details.bahanBaku', 'details.status']);

        if ($outletId = $user->outlets()->first()?->id) {
            $query->where('outlet_id', $outletId);
        }

        $permintaans = $query->orderByDesc('created_at')->paginate(15);

        return response()->json([
            'data' => $permintaans->map(fn ($p) => $this->format($p)),
            'meta' => [
                'current_page' => $permintaans->currentPage(),
                'last_page' => $permintaans->lastPage(),
                'per_page' => $permintaans->perPage(),
                'total' => $permintaans->total(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'outlet_id' => 'required|exists:outlets,id',
            'items' => 'required|array|min:1',
            'items.*.tipe_item' => 'required|in:bahan_baku,mesin',
            'items.*.bahan_baku_id' => 'nullable|exists:bahan_bakus,id',
            'items.*.mesin_id' => 'nullable|exists:mesins,id',
            'items.*.jumlah_diminta' => 'required|numeric|min:0.0001',
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

        $permintaan = DB::transaction(function () use ($request) {
            $diajukan = Status::where('konteks', 'permintaan_stok_outlet')->where('kode', 'diajukan')->first();
            $detailDiajukan = Status::where('konteks', 'permintaan_stok_outlet_detail')->where('kode', 'diajukan')->first();

            $permintaan = PermintaanStokOutlet::create([
                'outlet_id' => $request->outlet_id,
                'user_id' => $request->user()->id,
                'tanggal' => now(),
                'status_id' => $diajukan?->id,
            ]);

            foreach ($request->items as $item) {
                PermintaanStokOutletDetail::create([
                    'permintaan_stok_outlet_id' => $permintaan->id,
                    'bahan_baku_id' => $item['tipe_item'] === 'bahan_baku' ? $item['bahan_baku_id'] : null,
                    'mesin_id' => $item['tipe_item'] === 'mesin' ? $item['mesin_id'] : null,
                    'tipe_item' => $item['tipe_item'],
                    'jumlah_diminta' => $item['jumlah_diminta'],
                    'status_id' => $detailDiajukan?->id,
                ]);
            }

            return $permintaan;
        });

        return response()->json([
            'message' => 'Permintaan stok berhasil diajukan.',
            'data' => $this->format($permintaan->fresh()->load(['outlet', 'user', 'status', 'details.bahanBaku', 'details.mesin', 'details.status'])),
        ], 201);
    }

    public function show(PermintaanStokOutlet $permintaan): JsonResponse
    {
        $permintaan->load(['outlet', 'user', 'status', 'details.bahanBaku', 'details.mesin', 'details.status', 'distribusiOutlets.details.bahanBaku', 'distribusiOutlets.details.mesin']);
        return response()->json(['data' => $this->format($permintaan)]);
    }

    public function destroy(PermintaanStokOutlet $permintaan): JsonResponse
    {
        $diajukan = Status::where('konteks', 'permintaan_stok_outlet')->where('kode', 'diajukan')->first();
        if ($permintaan->status_id !== $diajukan?->id) {
            return response()->json(['message' => 'Hanya bisa dihapus saat status diajukan.'], 422);
        }

        $permintaan->delete();
        return response()->json(['message' => 'Permintaan berhasil dihapus.']);
    }

    /**
     * Tim Pengadaan validasi per item
     */
    public function validateItems(Request $request, PermintaanStokOutlet $permintaan): JsonResponse
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.detail_id' => 'required|exists:permintaan_stok_outlet_details,id',
            'items.*.status' => 'required|in:disetujui,disetujui_sebagian,ditolak',
            'items.*.jumlah_disetujui' => 'nullable|numeric|min:0',
            'items.*.alasan' => 'nullable|string',
        ]);

        DB::transaction(function () use ($request, $permintaan) {
            foreach ($request->items as $item) {
                $detail = PermintaanStokOutletDetail::where('id', $item['detail_id'])
                    ->where('permintaan_stok_outlet_id', $permintaan->id)->firstOrFail();

                $status = Status::where('konteks', 'permintaan_stok_outlet_detail')
                    ->where('kode', $item['status'])->first();

                $update = ['status_id' => $status?->id];

                if ($item['status'] === 'disetujui') {
                    $update['jumlah_disetujui'] = $detail->jumlah_diminta;
                } elseif ($item['status'] === 'disetujui_sebagian') {
                    $update['jumlah_disetujui'] = $item['jumlah_disetujui'] ?? $detail->jumlah_diminta;
                    $update['alasan'] = $item['alasan'] ?? null;
                } else {
                    $update['alasan'] = $item['alasan'] ?? null;
                }

                $detail->update($update);
            }

            // Rollup status header
            $this->rollupStatus($permintaan);
        });

        return response()->json(['message' => 'Validasi berhasil.']);
    }

    private function rollupStatus(PermintaanStokOutlet $permintaan): void
    {
        $details = $permintaan->details()->with('status')->get();
        $total = $details->count();

        $ditolak = $details->filter(fn ($d) => $d->status?->kode === 'ditolak')->count();
        $disetujui = $details->filter(fn ($d) => $d->status?->kode === 'disetujui')->count();
        $disetujuiSebagian = $details->filter(fn ($d) => $d->status?->kode === 'disetujui_sebagian')->count();

        if ($ditolak === $total) {
            $kode = 'ditolak';
        } elseif ($disetujui === $total) {
            $kode = 'disetujui';
        } else {
            $kode = 'disetujui_sebagian';
        }

        $status = Status::where('konteks', 'permintaan_stok_outlet')->where('kode', $kode)->first();
        $permintaan->update(['status_id' => $status?->id]);
    }

    private function format($p): array
    {
        return [
            'id' => $p->id,
            'outlet' => $p->outlet ? ['id' => $p->outlet->id, 'nama' => $p->outlet->nama] : null,
            'user' => $p->user ? ['id' => $p->user->id, 'nama' => $p->user->nama] : null,
            'tanggal' => $p->tanggal?->format('Y-m-d'),
            'status' => $p->status ? ['id' => $p->status->id, 'kode' => $p->status->kode, 'label' => $p->status->label] : null,
            'details' => $p->details->map(fn ($d) => [
                'id' => $d->id,
                'tipe_item' => $d->tipe_item,
                'bahan_baku' => $d->bahanBaku ? ['id' => $d->bahanBaku->id, 'nama' => $d->bahanBaku->nama] : null,
                'mesin' => $d->mesin ? ['id' => $d->mesin->id, 'nama' => $d->mesin->nama] : null,
                'jumlah_diminta' => (float) $d->jumlah_diminta,
                'jumlah_disetujui' => $d->jumlah_disetujui !== null ? (float) $d->jumlah_disetujui : null,
                'alasan' => $d->alasan,
                'status' => $d->status ? ['kode' => $d->status->kode, 'label' => $d->status->label] : null,
            ]),
            'created_at' => $p->created_at?->toDateTimeString(),
        ];
    }
}
