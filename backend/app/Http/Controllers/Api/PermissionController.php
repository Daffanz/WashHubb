<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PermissionResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return PermissionResource::collection(Permission::paginate(50));
    }

    public function show(Permission $permission): JsonResponse
    {
        return response()->json(['data' => new PermissionResource($permission)]);
    }
}
