<?php

namespace App\Http\Controllers\Api\Master;

use App\Http\Controllers\Controller;
use App\Http\Requests\Master\StoreBahanBakuRequest;
use App\Http\Requests\Master\UpdateBahanBakuRequest;
use App\Http\Resources\Master\BahanBakuResource;
use App\Models\BahanBaku;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class BahanBakuController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return BahanBakuResource::collection(
            BahanBaku::with('kategori')->paginate(15)
        );
    }

    public function store(StoreBahanBakuRequest $request): JsonResponse
    {
        $bahanBaku = BahanBaku::create($request->validated());

        return response()->json([
            'message' => 'Bahan baku berhasil dibuat.',
            'data'    => new BahanBakuResource($bahanBaku->load('kategori')),
        ], 201);
    }

    public function show(BahanBaku $bahanBaku): JsonResponse
    {
        return response()->json([
            'data' => new BahanBakuResource($bahanBaku->load('kategori')),
        ]);
    }

    public function update(UpdateBahanBakuRequest $request, BahanBaku $bahanBaku): JsonResponse
    {
        $bahanBaku->update($request->validated());

        return response()->json([
            'message' => 'Bahan baku berhasil diperbarui.',
            'data'    => new BahanBakuResource($bahanBaku->load('kategori')),
        ]);
    }

    public function destroy(BahanBaku $bahanBaku): JsonResponse
    {
        $bahanBaku->delete();

        return response()->json(['message' => 'Bahan baku berhasil dihapus.']);
    }
}
