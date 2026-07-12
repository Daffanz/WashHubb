<?php

namespace App\Http\Controllers\Api\Master;

use App\Http\Controllers\Controller;
use App\Http\Requests\Master\StoreMesinRequest;
use App\Models\Mesin;
use App\Models\Status;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MesinController extends Controller
{
    public function index(): JsonResponse
    {
        $mesins = Mesin::with('status')->paginate(15);

        return response()->json([
            'data' => $mesins->map(fn ($m) => $this->formatMesin($m)),
            'meta' => [
                'current_page' => $mesins->currentPage(),
                'last_page'    => $mesins->lastPage(),
                'per_page'     => $mesins->perPage(),
                'total'        => $mesins->total(),
            ],
        ]);
    }

    public function store(StoreMesinRequest $request): JsonResponse
    {
        $data = $request->validated();
        if (empty($data['status_id'])) {
            $aktifStatus = Status::where('konteks', 'mesin')->where('kode', 'aktif')->first();
            $data['status_id'] = $aktifStatus?->id;
        }

        $mesin = Mesin::create($data);

        return response()->json([
            'message' => 'Mesin berhasil dibuat.',
            'data'    => $this->formatMesin($mesin->load('status')),
        ], 201);
    }

    public function show(Mesin $mesin): JsonResponse
    {
        return response()->json([
            'data' => $this->formatMesin($mesin->load('status')),
        ]);
    }

    public function update(Request $request, Mesin $mesin): JsonResponse
    {
        $request->validate([
            'nama'       => 'sometimes|string|max:255',
            'kode_mesin' => 'sometimes|string|max:255|unique:mesins,kode_mesin,' . $mesin->id,
            'merk'       => 'sometimes|nullable|string|max:255',
            'tipe'       => 'sometimes|nullable|string|max:255',
            'kapasitas'  => 'sometimes|nullable|integer|min:0',
            'harga_standar' => 'sometimes|numeric|min:0',
        ]);

        $mesin->update($request->only(['nama', 'kode_mesin', 'merk', 'tipe', 'kapasitas', 'harga_standar']));

        return response()->json([
            'message' => 'Mesin berhasil diperbarui.',
            'data'    => $this->formatMesin($mesin->load('status')),
        ]);
    }

    public function destroy(Mesin $mesin): JsonResponse
    {
        $mesin->delete();
        return response()->json(['message' => 'Mesin berhasil dihapus.']);
    }

    private function formatMesin($m): array
    {
        return [
            'id'         => $m->id,
            'nama'       => $m->nama,
            'kode_mesin' => $m->kode_mesin,
            'merk'       => $m->merk,
            'tipe'       => $m->tipe,
            'kapasitas'    => $m->kapasitas,
            'harga_standar' => (float) $m->harga_standar,
            'status'       => $m->status ? ['id' => $m->status->id, 'kode' => $m->status->kode, 'label' => $m->status->label] : null,
            'created_at' => $m->created_at?->toDateTimeString(),
        ];
    }
}
