<?php

namespace App\Http\Controllers\Api\Inventory;

use App\Http\Controllers\Controller;
use App\Http\Requests\Inventory\StoreMutasiRequest;
use App\Http\Resources\Inventory\MutasiResource;
use App\Models\MutasiStok;
use App\Services\StockService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class MutasiController extends Controller
{
    public function __construct(
        private readonly StockService $stockService,
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $query = MutasiStok::with('stok');

        if ($request->filled('stok_type') && $request->filled('stok_id')) {
            $query->where('stok_type', $request->stok_type)
                  ->where('stok_id', $request->stok_id);
        }

        if ($request->filled('jenis_mutasi')) {
            $query->where('jenis_mutasi', $request->jenis_mutasi);
        }

        if ($request->filled('dari') && $request->filled('sampai')) {
            $query->whereBetween('tanggal', [$request->dari, $request->sampai]);
        }

        return MutasiResource::collection($query->orderByDesc('tanggal')->paginate(15));
    }

    public function store(StoreMutasiRequest $request): JsonResponse
    {
        $mutasi = $this->stockService->adjustStock(
            stokType:    $request->stok_type,
            stokId:      $request->stok_id,
            jumlah:      $request->jumlah,
            jenisMutasi: $request->jenis_mutasi,
            keterangan:  $request->keterangan,
        );

        return response()->json([
            'message' => 'Mutasi stok berhasil dicatat.',
            'data'    => new MutasiResource($mutasi->load('stok')),
        ], 201);
    }
}
