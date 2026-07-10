<?php

namespace App\Http\Controllers\Api\Procurement;

use App\Http\Controllers\Controller;
use App\Http\Requests\Procurement\StorePenerimaanRequest;
use App\Http\Resources\Procurement\PenerimaanResource;
use App\Models\PenerimaanBarang;
use App\Models\Status;
use App\Services\StockService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;

class PenerimaanController extends Controller
{
    public function __construct(
        private readonly StockService $stockService,
    ) {}

    public function index(): AnonymousResourceCollection
    {
        return PenerimaanResource::collection(
            PenerimaanBarang::with('distribusiBarang.purchaseOrder')->paginate(15)
        );
    }

    public function store(StorePenerimaanRequest $request): JsonResponse
    {
        $penerimaan = DB::transaction(function () use ($request) {
            $penerimaan = PenerimaanBarang::create($request->validated());

            // Update distribution status to 'diterima'
            $diterimaStatus = Status::where('group', 'distribusi')->where('name', 'diterima')->first();
            $penerimaan->distribusiBarang->update(['status_id' => $diterimaStatus?->id]);

            // Update stock from receipt
            $this->stockService->updateFromReceipt($penerimaan);

            return $penerimaan;
        });

        return response()->json([
            'message' => 'Penerimaan berhasil dicatat dan stok diperbarui.',
            'data'    => new PenerimaanResource($penerimaan->load('distribusiBarang.purchaseOrder')),
        ], 201);
    }

    public function show(PenerimaanBarang $penerimaan): JsonResponse
    {
        return response()->json([
            'data' => new PenerimaanResource($penerimaan->load('distribusiBarang.purchaseOrder')),
        ]);
    }
}
