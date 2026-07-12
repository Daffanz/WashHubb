<?php

namespace App\Http\Controllers\Api\Master;

use App\Http\Controllers\Controller;
use App\Http\Requests\Master\StoreJenisLayananRequest;
use App\Models\JenisLayanan;
use App\Models\Status;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class JenisLayananController extends Controller
{
    public function index(): JsonResponse
    {
        $layanans = JenisLayanan::with('materials', 'status')->paginate(15);

        return response()->json([
            'data' => $layanans->map(fn ($l) => $this->formatLayanan($l)),
            'meta' => [
                'current_page' => $layanans->currentPage(),
                'last_page'    => $layanans->lastPage(),
                'per_page'     => $layanans->perPage(),
                'total'        => $layanans->total(),
            ],
        ]);
    }

    public function store(StoreJenisLayananRequest $request): JsonResponse
    {
        $data = $request->validated();
        if (empty($data['status_id'])) {
            $aktifStatus = Status::where('konteks', 'jenis_layanan')->where('kode', 'aktif')->first();
            $data['status_id'] = $aktifStatus?->id;
        }

        $layanan = JenisLayanan::create($data);

        return response()->json([
            'message' => 'Jenis layanan berhasil dibuat.',
            'data'    => $this->formatLayanan($layanan->load(['materials', 'status'])),
        ], 201);
    }

    public function show(JenisLayanan $jenisLayanan): JsonResponse
    {
        return response()->json([
            'data' => $this->formatLayanan($jenisLayanan->load(['materials', 'status'])),
        ]);
    }

    public function update(Request $request, JenisLayanan $jenisLayanan): JsonResponse
    {
        $request->validate([
            'nama'                 => 'sometimes|string|max:255',
            'harga_standar_per_kg' => 'sometimes|numeric|min:0',
        ]);

        $jenisLayanan->update($request->only(['nama', 'harga_standar_per_kg']));

        return response()->json([
            'message' => 'Jenis layanan berhasil diperbarui.',
            'data'    => $this->formatLayanan($jenisLayanan->load(['materials', 'status'])),
        ]);
    }

    public function destroy(JenisLayanan $jenisLayanan): JsonResponse
    {
        $jenisLayanan->delete();
        return response()->json(['message' => 'Jenis layanan berhasil dihapus.']);
    }

    public function attachMaterial(Request $request, JenisLayanan $jenisLayanan): JsonResponse
    {
        $request->validate([
            'bahan_baku_id'   => 'required|exists:bahan_bakus,id',
            'jumlah_konsumsi' => 'required|numeric|min:0.0001',
        ]);

        $jenisLayanan->materials()->syncWithoutDetaching([
            $request->bahan_baku_id => ['jumlah_konsumsi' => $request->jumlah_konsumsi],
        ]);

        return response()->json([
            'message' => 'Material berhasil ditambahkan ke layanan.',
            'data'    => $this->formatLayanan($jenisLayanan->fresh()->load(['materials', 'status'])),
        ]);
    }

    public function detachMaterial(JenisLayanan $jenisLayanan, \App\Models\BahanBaku $bahanBaku): JsonResponse
    {
        $jenisLayanan->materials()->detach($bahanBaku->id);

        return response()->json([
            'message' => 'Material berhasil dihapus dari layanan.',
            'data'    => $this->formatLayanan($jenisLayanan->fresh()->load(['materials', 'status'])),
        ]);
    }

    private function formatLayanan($l): array
    {
        return [
            'id'                   => $l->id,
            'nama'                 => $l->nama,
            'harga_standar_per_kg' => (float) $l->harga_standar_per_kg,
            'status'               => $l->status ? ['id' => $l->status->id, 'kode' => $l->status->kode, 'label' => $l->status->label] : null,
            'materials'            => $l->materials->map(fn ($m) => [
                'id'              => $m->id,
                'nama'            => $m->nama,
                'satuan'          => $m->satuan,
                'harga_standar'   => (float) $m->harga_standar,
                'jumlah_konsumsi' => (float) $m->pivot->jumlah_konsumsi,
            ]),
            'created_at' => $l->created_at?->toDateTimeString(),
        ];
    }
}
