<?php
namespace App\Http\Controllers\Api\Operasional;

use App\Http\Controllers\Controller;
use App\Models\OrderCucian;
use App\Models\DetailMesin;
use App\Models\JenisLayananBahanBaku;
use App\Models\StokOutletBahanBaku;
use App\Models\MutasiStokOutletBahanBaku;
use App\Models\JadwalServiceMesin;
use App\Models\Status;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderCucianController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $query = OrderCucian::with(['outlet', 'user', 'jenisLayanan', 'detailMesin.mesin', 'status']);

        // Filter by outlet if user is manajer outlet
        if ($outletId = $user->outlets()->first()?->id) {
            $query->where('outlet_id', $outletId);
        }

        $orders = $query->orderByDesc('waktu_masuk')->paginate(15);

        return response()->json([
            'data' => $orders->map(fn ($o) => $this->formatOrder($o)),
            'meta' => [
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
                'per_page' => $orders->perPage(),
                'total' => $orders->total(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'outlet_id' => 'required|exists:outlets,id',
            'jenis_layanan_id' => 'required|exists:jenis_layanans,id',
            'detail_mesin_id' => 'required|exists:detail_mesins,id',
            'berat' => 'required|numeric|min:0.01',
        ]);

        $order = DB::transaction(function () use ($request) {
            // Cek mesin aktif
            $mesin = DetailMesin::findOrFail($request->detail_mesin_id);
            $aktifStatus = Status::where('konteks', 'detail_mesin')->where('kode', 'aktif')->first();
            if ($mesin->status_id !== $aktifStatus?->id) {
                throw new \InvalidArgumentException('Mesin tidak aktif.');
            }

            // Hitung total harga
            $jenisLayanan = \App\Models\JenisLayanan::findOrFail($request->jenis_layanan_id);
            $totalHarga = $jenisLayanan->harga_standar_per_kg * $request->berat;

            // Hitung estimasi selesai (default 2 jam)
            $waktuMasuk = now();
            $estimasiSelesai = $waktuMasuk->copy()->addHours(2);

            // Status diproses
            $diproses = Status::where('konteks', 'order_cucian')->where('kode', 'diproses')->first();

            $order = OrderCucian::create([
                'outlet_id' => $request->outlet_id,
                'user_id' => $request->user()->id,
                'jenis_layanan_id' => $request->jenis_layanan_id,
                'detail_mesin_id' => $request->detail_mesin_id,
                'berat' => $request->berat,
                'total_harga' => $totalHarga,
                'waktu_masuk' => $waktuMasuk,
                'estimasi_selesai' => $estimasiSelesai,
                'status_id' => $diproses?->id,
            ]);

            // Kurangi stok outlet bahan baku
            $this->decreaseOutletStock($order);

            // Ubah status mesin → digunakan
            $digunakan = Status::where('konteks', 'detail_mesin')->where('kode', 'digunakan')->first();
            $mesin->update(['status_id' => $digunakan?->id]);

            return $order;
        });

        return response()->json([
            'message' => 'Order cucian berhasil dibuat.',
            'data' => $this->formatOrder($order->fresh()->load(['outlet', 'user', 'jenisLayanan', 'detailMesin.mesin', 'status'])),
        ], 201);
    }

    public function show(OrderCucian $order): JsonResponse
    {
        $order->load(['outlet', 'user', 'jenisLayanan', 'detailMesin.mesin', 'status']);
        return response()->json(['data' => $this->formatOrder($order)]);
    }

    /**
     * Selesai / Batalkan order
     */
    public function update(Request $request, OrderCucian $order): JsonResponse
    {
        $request->validate([
            'status' => 'required|in:selesai,dibatalkan',
            'alasan_pembatalan' => 'required_if:status,dibatalkan|nullable|string',
        ]);

        DB::transaction(function () use ($request, $order) {
            if ($request->status === 'selesai') {
                $selesai = Status::where('konteks', 'order_cucian')->where('kode', 'selesai')->first();
                $order->update(['status_id' => $selesai?->id, 'waktu_selesai' => now()]);
            } else {
                $dibatalkan = Status::where('konteks', 'order_cucian')->where('kode', 'dibatalkan')->first();
                $order->update([
                    'status_id' => $dibatalkan?->id,
                    'alasan_pembatalan' => $request->alasan_pembatalan,
                ]);

                // Kembalikan stok outlet
                $this->reverseOutletStock($order);
            }

            // Resolve status mesin
            $this->resolveMachineStatus($order->detail_mesin_id);
        });

        return response()->json(['message' => 'Order berhasil diperbarui.']);
    }

    private function decreaseOutletStock(OrderCucian $order): void
    {
        $konsumsiList = JenisLayananBahanBaku::where('jenis_layanan_id', $order->jenis_layanan_id)->get();

        foreach ($konsumsiList as $konsumsi) {
            $jumlah = $konsumsi->konsumsi_per_kg * $order->berat;

            $stok = StokOutletBahanBaku::lockForUpdate()
                ->firstOrCreate(
                    ['outlet_id' => $order->outlet_id, 'bahan_baku_id' => $konsumsi->bahan_baku_id],
                    ['stok_saat_ini' => 0, 'stok_minimum' => 0, 'stok_masuk' => 0, 'stok_keluar' => 0]
                );

            if ($stok->stok_saat_ini < $jumlah) {
                throw new \InvalidArgumentException("Stok bahan baku {$konsumsi->bahanBaku->nama} tidak cukup.");
            }

            $stok->decrement('stok_saat_ini', $jumlah);
            $stok->increment('stok_keluar', $jumlah);

            MutasiStokOutletBahanBaku::create([
                'stok_outlet_bahan_baku_id' => $stok->id,
                'jenis_mutasi' => 'keluar',
                'jumlah' => $jumlah,
                'tanggal' => now(),
                'order_cucian_id' => $order->id,
            ]);
        }
    }

    private function reverseOutletStock(OrderCucian $order): void
    {
        $konsumsiList = JenisLayananBahanBaku::where('jenis_layanan_id', $order->jenis_layanan_id)->get();

        foreach ($konsumsiList as $konsumsi) {
            $jumlah = $konsumsi->konsumsi_per_kg * $order->berat;

            $stok = StokOutletBahanBaku::lockForUpdate()
                ->where('outlet_id', $order->outlet_id)
                ->where('bahan_baku_id', $konsumsi->bahan_baku_id)->first();

            if ($stok) {
                $stok->increment('stok_saat_ini', $jumlah);
                $stok->decrement('stok_keluar', $jumlah);

                MutasiStokOutletBahanBaku::create([
                    'stok_outlet_bahan_baku_id' => $stok->id,
                    'jenis_mutasi' => 'masuk',
                    'jumlah' => $jumlah,
                    'tanggal' => now(),
                    'order_cucian_id' => $order->id,
                ]);
            }
        }
    }

    private function resolveMachineStatus(int $detailMesinId): void
    {
        $mesin = DetailMesin::lockForUpdate()->findOrFail($detailMesinId);

        // Cek apakah ada jadwal service yang disetujui + menunggu mesin bebas
        $pendingService = JadwalServiceMesin::where('detail_mesin_id', $detailMesinId)
            ->where('menunggu_mesin_bebas', true)
            ->whereHas('status', fn ($q) => $q->where('kode', 'disetujui'))
            ->exists();

        if ($pendingService) {
            $maintenance = Status::where('konteks', 'detail_mesin')->where('kode', 'maintenance')->first();
            $mesin->update(['status_id' => $maintenance?->id]);

            // Update jadwal service: menunggu_mesin_bebas = false
            JadwalServiceMesin::where('detail_mesin_id', $detailMesinId)
                ->where('menunggu_mesin_bebas', true)
                ->whereHas('status', fn ($q) => $q->where('kode', 'disetujui'))
                ->update(['menunggu_mesin_bebas' => false]);
        } else {
            $aktif = Status::where('konteks', 'detail_mesin')->where('kode', 'aktif')->first();
            $mesin->update(['status_id' => $aktif?->id]);
        }
    }

    private function formatOrder($o): array
    {
        return [
            'id' => $o->id,
            'outlet' => $o->outlet ? ['id' => $o->outlet->id, 'nama' => $o->outlet->nama] : null,
            'user' => $o->user ? ['id' => $o->user->id, 'nama' => $o->user->nama] : null,
            'jenis_layanan' => $o->jenisLayanan ? ['id' => $o->jenisLayanan->id, 'nama' => $o->jenisLayanan->nama] : null,
            'mesin' => $o->detailMesin ? [
                'id' => $o->detailMesin->id,
                'nama' => $o->detailMesin->mesin?->nama,
                'nomor_seri' => $o->detailMesin->nomor_seri,
            ] : null,
            'berat' => (float) $o->berat,
            'total_harga' => (float) $o->total_harga,
            'waktu_masuk' => $o->waktu_masuk?->toDateTimeString(),
            'estimasi_selesai' => $o->estimasi_selesai?->toDateTimeString(),
            'waktu_selesai' => $o->waktu_selesai?->toDateTimeString(),
            'alasan_pembatalan' => $o->alasan_pembatalan,
            'status' => $o->status ? ['id' => $o->status->id, 'kode' => $o->status->kode, 'label' => $o->status->label] : null,
            'created_at' => $o->created_at?->toDateTimeString(),
        ];
    }
}
