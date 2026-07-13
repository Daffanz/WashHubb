<?php
namespace App\Http\Controllers\Api\Master;

use App\Http\Controllers\Controller;
use App\Http\Requests\Master\StoreBahanBakuRequest;
use App\Http\Requests\Master\UpdateBahanBakuRequest;
use App\Models\BahanBaku;
use App\Models\Status;
use Illuminate\Http\JsonResponse;

class BahanBakuController extends Controller
{
    public function index(\Illuminate\Http\Request $request): JsonResponse
    {
        $perPage = (int) $request->input('per_page', 15);
        $items = BahanBaku::with('kategori', 'status')->paginate($perPage);

        return response()->json([
            'data' => $items->map(fn ($b) => $this->formatBahanBaku($b)),
            'meta' => [
                'current_page' => $items->currentPage(),
                'last_page'    => $items->lastPage(),
                'per_page'     => $items->perPage(),
                'total'        => $items->total(),
            ],
        ]);
    }

    public function store(StoreBahanBakuRequest $request): JsonResponse
    {
        $data = $request->validated();
        if (empty($data['status_id'])) {
            $aktifStatus = Status::where('konteks', 'bahan_baku')->where('kode', 'aktif')->first();
            $data['status_id'] = $aktifStatus?->id;
        }

        $bahanBaku = BahanBaku::create($data);

        return response()->json([
            'message' => 'Bahan baku berhasil dibuat.',
            'data'    => $this->formatBahanBaku($bahanBaku->load(['kategori', 'status'])),
        ], 201);
    }

    public function show(BahanBaku $bahanBaku): JsonResponse
    {
        return response()->json([
            'data' => $this->formatBahanBaku($bahanBaku->load(['kategori', 'status'])),
        ]);
    }

    public function update(UpdateBahanBakuRequest $request, BahanBaku $bahanBaku): JsonResponse
    {
        $bahanBaku->update($request->validated());

        return response()->json([
            'message' => 'Bahan baku berhasil diperbarui.',
            'data'    => $this->formatBahanBaku($bahanBaku->load(['kategori', 'status'])),
        ]);
    }

    public function destroy(BahanBaku $bahanBaku): JsonResponse
    {
        $bahanBaku->delete();
        return response()->json(['message' => 'Bahan baku berhasil dihapus.']);
    }

    private function formatBahanBaku($b): array
    {
        return [
            'id'            => $b->id,
            'kategori'      => $b->kategori ? ['id' => $b->kategori->id, 'nama' => $b->kategori->nama] : null,
            'nama'          => $b->nama,
            'satuan'        => $b->satuan,
            'harga_standar' => (float) $b->harga_standar,
            'status'        => $b->status ? ['id' => $b->status->id, 'kode' => $b->status->kode, 'label' => $b->status->label] : null,
            'created_at'    => $b->created_at?->toDateTimeString(),
        ];
    }
}
