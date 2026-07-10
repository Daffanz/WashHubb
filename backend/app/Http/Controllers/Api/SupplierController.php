<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Supplier\StoreSupplierRequest;
use App\Http\Requests\Supplier\UpdateSupplierRequest;
use App\Http\Resources\SupplierResource;
use App\Models\Supplier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class SupplierController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return SupplierResource::collection(
            Supplier::with(['user', 'status'])->paginate(15)
        );
    }

    public function store(StoreSupplierRequest $request): JsonResponse
    {
        $supplier = Supplier::create($request->validated());

        return response()->json([
            'message' => 'Supplier berhasil dibuat.',
            'data'    => new SupplierResource($supplier->load(['user', 'status'])),
        ], 201);
    }

    public function show(Supplier $supplier): JsonResponse
    {
        return response()->json([
            'data' => new SupplierResource($supplier->load(['user', 'status'])),
        ]);
    }

    public function update(UpdateSupplierRequest $request, Supplier $supplier): JsonResponse
    {
        $supplier->update($request->validated());

        return response()->json([
            'message' => 'Supplier berhasil diperbarui.',
            'data'    => new SupplierResource($supplier->load(['user', 'status'])),
        ]);
    }

    public function destroy(Supplier $supplier): JsonResponse
    {
        $supplier->delete();

        return response()->json(['message' => 'Supplier berhasil dihapus.']);
    }
}
