<?php

namespace App\Http\Controllers\Api\Master;

use App\Http\Controllers\Controller;
use App\Http\Requests\Master\StoreMesinRequest;
use App\Http\Resources\Master\MesinResource;
use App\Models\Mesin;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class MesinController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return MesinResource::collection(
            Mesin::paginate(15)
        );
    }

    public function store(StoreMesinRequest $request): JsonResponse
    {
        $mesin = Mesin::create($request->validated());

        return response()->json([
            'message' => 'Mesin berhasil dibuat.',
            'data'    => new MesinResource($mesin),
        ], 201);
    }

    public function show(Mesin $mesin): JsonResponse
    {
        return response()->json([
            'data' => new MesinResource($mesin),
        ]);
    }

    public function update(Request $request, Mesin $mesin): JsonResponse
    {
        $request->validate([
            'nama'        => 'sometimes|string|max:255',
            'kode_mesin'  => 'sometimes|string|max:255|unique:mesins,kode_mesin,' . $mesin->id,
            'merk'        => 'sometimes|nullable|string|max:255',
            'tipe'        => 'sometimes|nullable|string|max:255',
            'kapasitas'   => 'sometimes|nullable|integer|min:0',
        ]);

        $mesin->update($request->only(['nama', 'kode_mesin', 'merk', 'tipe', 'kapasitas']));

        return response()->json([
            'message' => 'Mesin berhasil diperbarui.',
            'data'    => new MesinResource($mesin),
        ]);
    }

    public function destroy(Mesin $mesin): JsonResponse
    {
        $mesin->delete();

        return response()->json(['message' => 'Mesin berhasil dihapus.']);
    }
}
