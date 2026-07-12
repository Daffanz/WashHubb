<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
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

        if ($user->status?->kode === 'nonaktif') {
            auth()->logout();
            return response()->json(['message' => 'Akun Anda nonaktif.'], 403);
        }

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'message' => 'Login berhasil.',
            'data' => [
                'token' => $token,
                'user'  => $this->formatUser($user->load('role', 'status')),
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
            'data' => $this->formatUser($request->user()->load('role', 'role.permissions', 'status')),
        ]);
    }

    private function formatUser($user): array
    {
        return [
            'id'          => $user->id,
            'nama'        => $user->nama,
            'email'       => $user->email,
            'no_telp'     => $user->no_telp,
            'role'        => $user->role ? ['id' => $user->role->id, 'kode' => $user->role->kode, 'label' => $user->role->label] : null,
            'permissions' => $user->role?->permissions->pluck('kode') ?? [],
            'status'      => $user->status ? ['id' => $user->status->id, 'kode' => $user->status->kode, 'label' => $user->status->label] : null,
            'created_at'  => $user->created_at?->toDateTimeString(),
        ];
    }
}
