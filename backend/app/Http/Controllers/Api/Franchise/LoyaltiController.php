<?php
namespace App\Http\Controllers\Api\Franchise;

use App\Http\Controllers\Controller;
use App\Models\Loyalti;
use App\Models\LoyaltiPencairan;
use App\Models\OrderCucian;
use App\Models\Franchise;
use App\Models\Status;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LoyaltiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $query = Loyalti::with(['outlet', 'status', 'pencairans.status']);

        // Franchisee hanya lihat loyalti outlet miliknya
        if ($user->hasRole('franchisee')) {
            $franchise = Franchise::where('user_id', $user->id)->first();
            if ($franchise) {
                $query->whereHas('outlet', fn ($q) => $q->where('franchise_id', $franchise->id));
            }
        }

        $loyaltis = $query->orderByDesc('created_at')->paginate(15);

        return response()->json([
            'data' => $loyaltis->map(fn ($l) => $this->format($l)),
            'meta' => [
                'current_page' => $loyaltis->currentPage(),
                'last_page' => $loyaltis->lastPage(),
                'per_page' => $loyaltis->perPage(),
                'total' => $loyaltis->total(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'outlet_id' => 'required|exists:outlets,id',
            'periode' => 'required|string',
            'target_omset' => 'required|numeric|min:0',
            'target_operasional' => 'required|numeric|min:0|max:100',
        ]);

        $menunggu = Status::where('konteks', 'loyalti')->where('kode', 'menunggu_evaluasi')->first();

        $loyalti = Loyalti::create([
            'outlet_id' => $request->outlet_id,
            'periode' => $request->periode,
            'target_omset' => $request->target_omset,
            'target_operasional' => $request->target_operasional,
            'status_id' => $menunggu?->id,
        ]);

        return response()->json([
            'message' => 'Target loyalti berhasil dibuat.',
            'data' => $this->format($loyalti->fresh()->load(['outlet', 'status'])),
        ], 201);
    }

    public function show(Loyalti $loyalti): JsonResponse
    {
        $loyalti->load(['outlet', 'status', 'pencairans.status']);
        return response()->json(['data' => $this->format($loyalti)]);
    }

    /**
     * Franchisor input capaian_operasional → sistem evaluasi otomatis
     */
    public function evaluate(Request $request, Loyalti $loyalti): JsonResponse
    {
        $request->validate([
            'capaian_operasional' => 'required|numeric|min:0|max:100',
        ]);

        DB::transaction(function () use ($request, $loyalti) {
            $loyalti->update(['capaian_operasional' => $request->capaian_operasional]);

            // Hitung omset aktual dari order_cucian
            [$tahun, $bulan] = explode('-', $loyalti->periode);
            $omsetAktual = OrderCucian::where('outlet_id', $loyalti->outlet_id)
                ->whereYear('waktu_masuk', $tahun)
                ->whereMonth('waktu_masuk', $bulan)
                ->whereHas('status', fn ($q) => $q->where('kode', 'selesai'))
                ->sum('total_harga');

            $loyalti->update(['omset_aktual' => $omsetAktual]);

            // Evaluasi
            $memenuhi = $omsetAktual >= $loyalti->target_omset
                && $request->capaian_operasional >= $loyalti->target_operasional;

            $loyalti->update(['memenuhi_target' => $memenuhi]);

            $kode = $memenuhi ? 'memenuhi_target' : 'tidak_memenuhi_target';
            $status = Status::where('konteks', 'loyalti')->where('kode', $kode)->first();
            $loyalti->update(['status_id' => $status?->id]);
        });

        return response()->json(['message' => 'Evaluasi selesai.', 'data' => $this->format($loyalti->fresh()->load(['outlet', 'status']))]);
    }

    /**
     * Franchisor tetapkan bonus + keterangan
     */
    public function setBonus(Request $request, Loyalti $loyalti): JsonResponse
    {
        $request->validate([
            'jumlah_bonus' => 'required|numeric|min:0',
            'keterangan' => 'nullable|string',
        ]);

        $memenuhi = Status::where('konteks', 'loyalti')->where('kode', 'memenuhi_target')->first();
        if ($loyalti->status_id !== $memenuhi?->id) {
            return response()->json(['message' => 'Hanya bisa menetapkan bonus jika memenuhi target.'], 422);
        }

        $menungguPencairan = Status::where('konteks', 'loyalti')->where('kode', 'menunggu_pencairan')->first();
        $loyalti->update([
            'jumlah_bonus' => $request->jumlah_bonus,
            'keterangan' => $request->keterangan,
            'status_id' => $menungguPencairan?->id,
        ]);

        return response()->json(['message' => 'Bonus berhasil ditetapkan.']);
    }

    /**
     * Franchisor mencairkan bonus
     */
    public function cairkan(Request $request, Loyalti $loyalti): JsonResponse
    {
        $request->validate([
            'bukti_transfer' => 'required|string',
        ]);

        $menungguPencairan = Status::where('konteks', 'loyalti')->where('kode', 'menunggu_pencairan')->first();
        if ($loyalti->status_id !== $menungguPencairan?->id) {
            return response()->json(['message' => 'Status tidak valid untuk pencairan.'], 422);
        }

        DB::transaction(function () use ($request, $loyalti) {
            $diproses = Status::where('konteks', 'loyalti_pencairan')->where('kode', 'diproses')->first();
            $diprosesPencairan = Status::where('konteks', 'loyalti')->where('kode', 'diproses_pencairan')->first();

            LoyaltiPencairan::create([
                'loyalti_id' => $loyalti->id,
                'bukti_transfer' => $request->bukti_transfer,
                'status_id' => $diproses?->id,
            ]);

            $loyalti->update(['status_id' => $diprosesPencairan?->id]);
        });

        return response()->json(['message' => 'Pencairan berhasil diproses.']);
    }

    /**
     * Franchisee konfirmasi penerimaan bonus
     */
    public function confirmPencairan(Request $request, Loyalti $loyalti): JsonResponse
    {
        $request->validate([
            'status' => 'required|in:diterima,belum_diterima',
        ]);

        $diprosesPencairan = Status::where('konteks', 'loyalti')->where('kode', 'diproses_pencairan')->first();
        if ($loyalti->status_id !== $diprosesPencairan?->id) {
            return response()->json(['message' => 'Status tidak valid untuk konfirmasi.'], 422);
        }

        DB::transaction(function () use ($request, $loyalti) {
            $pencairan = $loyalti->pencairans()
                ->whereHas('status', fn ($q) => $q->where('kode', 'diproses'))
                ->latest()->first();

            if ($request->status === 'diterima') {
                $selesaiPencairan = Status::where('konteks', 'loyalti_pencairan')->where('kode', 'selesai')->first();
                $selesaiLoyalti = Status::where('konteks', 'loyalti')->where('kode', 'selesai')->first();

                $pencairan?->update(['status_id' => $selesaiPencairan?->id]);
                $loyalti->update(['status_id' => $selesaiLoyalti?->id]);
            } else {
                $gagalPencairan = Status::where('konteks', 'loyalti_pencairan')->where('kode', 'gagal')->first();
                $menungguPencairan = Status::where('konteks', 'loyalti')->where('kode', 'menunggu_pencairan')->first();

                $pencairan?->update(['status_id' => $gagalPencairan?->id]);
                $loyalti->update(['status_id' => $menungguPencairan?->id]);
            }
        });

        return response()->json(['message' => 'Konfirmasi berhasil.']);
    }

    private function format($l): array
    {
        return [
            'id' => $l->id,
            'outlet' => $l->outlet ? ['id' => $l->outlet->id, 'nama' => $l->outlet->nama] : null,
            'periode' => $l->periode,
            'target_omset' => (float) $l->target_omset,
            'target_operasional' => (float) $l->target_operasional,
            'omset_aktual' => (float) $l->omset_aktual,
            'capaian_operasional' => $l->capaian_operasional !== null ? (float) $l->capaian_operasional : null,
            'memenuhi_target' => $l->memenuhi_target,
            'jumlah_bonus' => $l->jumlah_bonus !== null ? (float) $l->jumlah_bonus : null,
            'keterangan' => $l->keterangan,
            'status' => $l->status ? ['id' => $l->status->id, 'kode' => $l->status->kode, 'label' => $l->status->label] : null,
            'pencairans' => $l->pencairans->map(fn ($p) => [
                'id' => $p->id,
                'bukti_transfer' => $p->bukti_transfer,
                'status' => $p->status ? ['kode' => $p->status->kode, 'label' => $p->status->label] : null,
                'created_at' => $p->created_at?->toDateTimeString(),
            ]),
            'created_at' => $l->created_at?->toDateTimeString(),
        ];
    }
}
