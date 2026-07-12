<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use Illuminate\Http\JsonResponse;

class PermissionController extends Controller
{
    public function index(): JsonResponse
    {
        $perms = Permission::paginate(50);
        return response()->json([
            'data' => $perms->map(fn ($p) => ['id' => $p->id, 'kode' => $p->kode, 'nama' => $p->nama, 'modul' => $p->modul]),
            'meta' => ['current_page' => $perms->currentPage(), 'last_page' => $perms->lastPage(), 'per_page' => $perms->perPage(), 'total' => $perms->total()],
        ]);
    }

    public function show(Permission $permission): JsonResponse
    {
        return response()->json(['data' => ['id' => $permission->id, 'kode' => $permission->kode, 'nama' => $permission->nama, 'modul' => $permission->modul]]);
    }
}
