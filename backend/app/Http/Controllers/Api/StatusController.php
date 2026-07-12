<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Status;
use Illuminate\Http\JsonResponse;

class StatusController extends Controller
{
    public function index(): JsonResponse
    {
        $statuses = Status::paginate(50);
        return response()->json([
            'data' => $statuses->map(fn ($s) => ['id' => $s->id, 'konteks' => $s->konteks, 'kode' => $s->kode, 'label' => $s->label]),
            'meta' => ['current_page' => $statuses->currentPage(), 'last_page' => $statuses->lastPage(), 'per_page' => $statuses->perPage(), 'total' => $statuses->total()],
        ]);
    }

    public function byContext(string $konteks): JsonResponse
    {
        $statuses = Status::where('konteks', $konteks)->get();
        return response()->json(['data' => $statuses->map(fn ($s) => ['id' => $s->id, 'kode' => $s->kode, 'label' => $s->label])]);
    }
}
