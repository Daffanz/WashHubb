<?php

namespace App\Http\Controllers\Api\Master;

use App\Http\Controllers\Controller;
use App\Http\Requests\Master\StoreKategoriRequest;
use App\Http\Requests\Master\UpdateKategoriRequest;
use App\Models\KategoriBahanBaku;
use Illuminate\Http\JsonResponse;

class KategoriController extends Controller
{
    public function index(): JsonResponse
    {
        $kategoris = KategoriBahanBaku::withCount('bahanBakus')->paginate(15);

        return response()->json([
            'data' => $kategoris->map(fn ($k) => [
                'id'               => $k->id,
                'nama'             => $k->nama,
                'bahan_bakus_count' => $k->bahan_bakus_count,
                'created_at'       => $k->created_at?->toDateTimeString(),
            ]),
            'meta' => [
                'current_page' => $kategoris->currentPage(),
                'last_page'    => $kategoris->lastPage(),
                'per_page'     => $kategoris->perPage(),
                'total'        => $kategoris->total(),
            ],
        ]);
    }

    public function store(StoreKategoriRequest $request): JsonResponse
    {
        $kategori = KategoriBahanBaku::create($request->validated());

        return response()->json([
            'message' => 'Kategori berhasil dibuat.',
            'data'    => ['id' => $kategori->id, 'nama' => $kategori->nama, 'created_at' => $kategori->created_at?->toDateTimeString()],
        ], 201);
    }

    public function show(KategoriBahanBaku $kategori): JsonResponse
    {
        return response()->json([
            'data' => [
                'id'               => $kategori->id,
                'nama'             => $kategori->nama,
                'bahan_bakus_count' => $kategori->bahanBakus()->count(),
                'created_at'       => $kategori->created_at?->toDateTimeString(),
            ],
        ]);
    }

    public function update(UpdateKategoriRequest $request, KategoriBahanBaku $kategori): JsonResponse
    {
        $kategori->update($request->validated());

        return response()->json([
            'message' => 'Kategori berhasil diperbarui.',
            'data'    => ['id' => $kategori->id, 'nama' => $kategori->nama],
        ]);
    }

    public function destroy(KategoriBahanBaku $kategori): JsonResponse
    {
        $kategori->delete();

        return response()->json(['message' => 'Kategori berhasil dihapus.']);
    }
}
