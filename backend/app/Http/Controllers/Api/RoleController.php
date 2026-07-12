<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function index(): JsonResponse
    {
        $roles = Role::with('permissions')->paginate(15);

        return response()->json([
            'data' => $roles->map(fn ($r) => [
                'id' => $r->id, 'kode' => $r->kode, 'label' => $r->label,
                'permissions' => $r->permissions->pluck('kode'),
            ]),
            'meta' => [
                'current_page' => $roles->currentPage(),
                'last_page'    => $roles->lastPage(),
                'per_page'     => $roles->perPage(),
                'total'        => $roles->total(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate(['kode' => 'required|string|unique:roles,kode', 'label' => 'required|string']);
        $role = Role::create($request->only('kode', 'label'));

        if ($request->filled('permissions')) {
            $ids = Permission::whereIn('kode', $request->permissions)->pluck('id');
            $role->permissions()->sync($ids);
        }

        return response()->json([
            'message' => 'Role berhasil dibuat.',
            'data'    => ['id' => $role->id, 'kode' => $role->kode, 'label' => $role->label, 'permissions' => $role->fresh()->permissions->pluck('kode')],
        ], 201);
    }

    public function show(Role $role): JsonResponse
    {
        return response()->json([
            'data' => ['id' => $role->id, 'kode' => $role->kode, 'label' => $role->label, 'permissions' => $role->permissions->pluck('kode')],
        ]);
    }

    public function update(Request $request, Role $role): JsonResponse
    {
        $request->validate(['kode' => 'sometimes|string|unique:roles,kode,' . $role->id, 'label' => 'sometimes|string']);
        $role->update($request->only('kode', 'label'));

        if ($request->has('permissions')) {
            $ids = Permission::whereIn('kode', $request->permissions)->pluck('id');
            $role->permissions()->sync($ids);
        }

        return response()->json([
            'message' => 'Role berhasil diperbarui.',
            'data'    => ['id' => $role->id, 'kode' => $role->kode, 'label' => $role->label, 'permissions' => $role->fresh()->permissions->pluck('kode')],
        ]);
    }

    public function destroy(Role $role): JsonResponse
    {
        $role->delete();
        return response()->json(['message' => 'Role berhasil dihapus.']);
    }
}
