<?php

namespace App\Http\Controllers\Api\Procurement;

use App\Http\Controllers\Controller;
use App\Http\Requests\Procurement\StoreDistribusiRequest;
use App\Http\Resources\Procurement\DistribusiResource;
use App\Models\DistribusiBarang;
use App\Models\Status;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class DistribusiController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return DistribusiResource::collection(
            DistribusiBarang::with(['purchaseOrder', 'status', 'penerimaanBarangs'])->paginate(15)
        );
    }

    public function store(StoreDistribusiRequest $request): JsonResponse
    {
        $diprosesStatus = Status::where('group', 'distribusi')->where('name', 'diproses')->first();

        $distribusi = DistribusiBarang::create([
            'po_id'       => $request->po_id,
            'status_id'   => $diprosesStatus?->id,
            'catatan'     => $request->catatan,
        ]);

        return response()->json([
            'message' => 'Distribusi berhasil dibuat.',
            'data'    => new DistribusiResource($distribusi->load(['purchaseOrder', 'status'])),
        ], 201);
    }

    public function show(DistribusiBarang $distribusi): JsonResponse
    {
        return response()->json([
            'data' => new DistribusiResource($distribusi->load(['purchaseOrder', 'status', 'penerimaanBarangs'])),
        ]);
    }
}
