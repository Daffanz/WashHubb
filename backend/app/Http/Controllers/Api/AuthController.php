<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function login(LoginRequest $request): JsonResponse
    {
        if (! auth()->attempt($request->only('email', 'password'))) {
            return response()->json(['message' => 'Kredensial tidak valid.'], 401);
        }

        $user = auth()->user();

        if ($user->status?->name === 'nonaktif') {
            auth()->logout();
            return response()->json(['message' => 'Akun Anda nonaktif.'], 403);
        }

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'message' => 'Login berhasil.',
            'data' => [
                'token' => $token,
                'user'  => new UserResource($user->load('roles', 'status')),
            ],
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logout berhasil.']);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'data' => new UserResource($request->user()->load('roles', 'permissions', 'status')),
        ]);
    }
}
