<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\RoleResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return RoleResource::collection(Role::with('permissions')->paginate(15));
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate(['name' => 'required|string|unique:roles,name']);

        $role = Role::create(['name' => $request->name, 'guard_name' => 'web']);

        if ($request->filled('permissions')) {
            $role->syncPermissions($request->permissions);
        }

        return response()->json([
            'message' => 'Role berhasil dibuat.',
            'data'    => new RoleResource($role->load('permissions')),
        ], 201);
    }

    public function show(Role $role): JsonResponse
    {
        return response()->json(['data' => new RoleResource($role->load('permissions'))]);
    }

    public function update(Request $request, Role $role): JsonResponse
    {
        $request->validate([
            'name'        => 'sometimes|string|unique:roles,name,' . $role->id,
            'permissions' => 'sometimes|array',
        ]);

        $role->update($request->only('name'));

        if ($request->filled('permissions')) {
            $role->syncPermissions($request->permissions);
        }

        return response()->json([
            'message' => 'Role berhasil diperbarui.',
            'data'    => new RoleResource($role->load('permissions')),
        ]);
    }

    public function destroy(Role $role): JsonResponse
    {
        $role->delete();

        return response()->json(['message' => 'Role berhasil dihapus.']);
    }
}
