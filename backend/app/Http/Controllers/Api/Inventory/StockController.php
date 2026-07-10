<?php

namespace App\Http\Controllers\Api\Inventory;

use App\Http\Controllers\Controller;
use App\Http\Resources\Inventory\StockResource;
use App\Models\StokPusatBahanBaku;
use App\Models\StokPusatMesin;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class StockController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $type = $request->query('type', 'bahan');

        if ($type === 'mesin') {
            return StockResource::collection(
                StokPusatMesin::with('mesin')->paginate(15)
            );
        }

        return StockResource::collection(
            StokPusatBahanBaku::with('bahanBaku.kategori')->paginate(15)
        );
    }

    public function show(string $type, int $id): JsonResponse
    {
        if ($type === 'mesin') {
            $stock = StokPusatMesin::with('mesin')->findOrFail($id);
        } else {
            $stock = StokPusatBahanBaku::with('bahanBaku.kategori')->findOrFail($id);
        }

        return response()->json([
            'data' => new StockResource($stock),
        ]);
    }
}
