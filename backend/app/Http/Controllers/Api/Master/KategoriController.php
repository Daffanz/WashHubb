<?php

namespace App\Http\Controllers\Api\Master;

use App\Http\Controllers\Controller;
use App\Http\Requests\Master\StoreKategoriRequest;
use App\Http\Requests\Master\UpdateKategoriRequest;
use App\Http\Resources\Master\KategoriResource;
use App\Models\KategoriBahanBaku;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class KategoriController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return KategoriResource::collection(
            KategoriBahanBaku::withCount('bahanBakus')->paginate(15)
        );
    }

    public function store(StoreKategoriRequest $request): JsonResponse
    {
        $kategori = KategoriBahanBaku::create($request->validated());

        return response()->json([
            'message' => 'Kategori berhasil dibuat.',
            'data'    => new KategoriResource($kategori),
        ], 201);
    }

    public function show(KategoriBahanBaku $kategori): JsonResponse
    {
        return response()->json([
            'data' => new KategoriResource($kategori->loadCount('bahanBakus')),
        ]);
    }

    public function update(UpdateKategoriRequest $request, KategoriBahanBaku $kategori): JsonResponse
    {
        $kategori->update($request->validated());

        return response()->json([
            'message' => 'Kategori berhasil diperbarui.',
            'data'    => new KategoriResource($kategori),
        ]);
    }

    public function destroy(KategoriBahanBaku $kategori): JsonResponse
    {
        $kategori->delete();

        return response()->json(['message' => 'Kategori berhasil dihapus.']);
    }
}
