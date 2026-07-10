<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class UserController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return UserResource::collection(
            User::with('roles', 'status')->paginate(15)
        );
    }

    public function store(StoreUserRequest $request): JsonResponse
    {
        $user = User::create($request->validated());
        $user->assignRole($request->role);

        return response()->json([
            'message' => 'User berhasil dibuat.',
            'data'    => new UserResource($user->load('roles', 'status')),
        ], 201);
    }

    public function show(User $user): JsonResponse
    {
        return response()->json([
            'data' => new UserResource($user->load('roles', 'status')),
        ]);
    }

    public function update(UpdateUserRequest $request, User $user): JsonResponse
    {
        $user->update($request->validated());

        if ($request->filled('role')) {
            $user->syncRoles([$request->role]);
        }

        return response()->json([
            'message' => 'User berhasil diperbarui.',
            'data'    => new UserResource($user->load('roles', 'status')),
        ]);
    }

    public function destroy(User $user): JsonResponse
    {
        $user->tokens()->delete();
        $user->delete();

        return response()->json(['message' => 'User berhasil dihapus.']);
    }
}
