<?php

namespace App\Http\Controllers\Api\Master;

use App\Http\Controllers\Controller;
use App\Http\Requests\Master\StoreJenisLayananRequest;
use App\Http\Resources\Master\JenisLayananResource;
use App\Models\JenisLayanan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class JenisLayananController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return JenisLayananResource::collection(
            JenisLayanan::with('materials')->paginate(15)
        );
    }

    public function store(StoreJenisLayananRequest $request): JsonResponse
    {
        $layanan = JenisLayanan::create($request->validated());

        return response()->json([
            'message' => 'Jenis layanan berhasil dibuat.',
            'data'    => new JenisLayananResource($layanan->load('materials')),
        ], 201);
    }

    public function show(JenisLayanan $jenisLayanan): JsonResponse
    {
        return response()->json([
            'data' => new JenisLayananResource($jenisLayanan->load('materials')),
        ]);
    }

    public function update(Request $request, JenisLayanan $jenisLayanan): JsonResponse
    {
        $request->validate([
            'nama'                => 'sometimes|string|max:255',
            'harga_standar_per_kg' => 'sometimes|numeric|min:0',
        ]);

        $jenisLayanan->update($request->only(['nama', 'harga_standar_per_kg']));

        return response()->json([
            'message' => 'Jenis layanan berhasil diperbarui.',
            'data'    => new JenisLayananResource($jenisLayanan->load('materials')),
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
            'bahan_baku_id'    => 'required|exists:bahan_bakus,id',
            'jumlah_konsumsi'  => 'required|numeric|min:0.0001',
        ]);

        $jenisLayanan->materials()->syncWithoutDetaching([
            $request->bahan_baku_id => ['jumlah_konsumsi' => $request->jumlah_konsumsi],
        ]);

        return response()->json([
            'message' => 'Material berhasil ditambahkan ke layanan.',
            'data'    => new JenisLayananResource($jenisLayanan->fresh()->load('materials')),
        ]);
    }

    public function detachMaterial(JenisLayanan $jenisLayanan, \App\Models\BahanBaku $bahanBaku): JsonResponse
    {
        $jenisLayanan->materials()->detach($bahanBaku->id);

        return response()->json([
            'message' => 'Material berhasil dihapus dari layanan.',
            'data'    => new JenisLayananResource($jenisLayanan->fresh()->load('materials')),
        ]);
    }
}
