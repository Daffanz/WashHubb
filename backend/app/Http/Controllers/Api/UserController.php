<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Models\Franchise;
use App\Models\ManajerOperasional;
use App\Models\User;
use App\Models\Status;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $users = User::with(['role', 'status'])->paginate(15);

        return response()->json([
            'data' => $users->map(fn ($u) => $this->formatUser($u)),
            'meta' => [
                'current_page' => $users->currentPage(),
                'last_page'    => $users->lastPage(),
                'per_page'     => $users->perPage(),
                'total'        => $users->total(),
            ],
        ]);
    }

    public function store(StoreUserRequest $request): JsonResponse
    {
        $data = $request->validated();
        // Auto-set status to "aktif" if not provided
        if (empty($data['status_id'])) {
            $aktifStatus = Status::where('konteks', 'akun')->where('kode', 'aktif')->first();
            $data['status_id'] = $aktifStatus?->id;
        }

        $user = User::create($data);

        // Auto-create Franchise record if role is franchise
        if ($user->role?->kode === 'franchise') {
            Franchise::firstOrCreate(['user_id' => $user->id]);
        }

        // Auto-create ManajerOperasional record if role is manager_outlet
        if ($user->role?->kode === 'manager_outlet') {
            ManajerOperasional::firstOrCreate(['user_id' => $user->id]);
        }

        return response()->json([
            'message' => 'User berhasil dibuat.',
            'data'    => $this->formatUser($user->load(['role', 'status'])),
        ], 201);
    }

    public function show(User $user): JsonResponse
    {
        return response()->json([
            'data' => $this->formatUser($user->load(['role', 'status'])),
        ]);
    }

    public function update(UpdateUserRequest $request, User $user): JsonResponse
    {
        $user->update($request->validated());

        // Auto-create Franchise record if role changed to franchise
        if ($user->role?->kode === 'franchise') {
            Franchise::firstOrCreate(['user_id' => $user->id]);
        }

        // Auto-create ManajerOperasional record if role changed to manager_outlet
        if ($user->role?->kode === 'manager_outlet') {
            ManajerOperasional::firstOrCreate(['user_id' => $user->id]);
        }

        return response()->json([
            'message' => 'User berhasil diperbarui.',
            'data'    => $this->formatUser($user->fresh()->load(['role', 'status'])),
        ]);
    }

    public function destroy(User $user): JsonResponse
    {
        if (in_array($user->role?->kode, ['admin_it', 'franchise', 'manager_outlet'])) {
            return response()->json(['message' => 'User dengan role ini tidak dapat dihapus.'], 403);
        }

        $user->tokens()->delete();
        $user->delete();
        return response()->json(['message' => 'User berhasil dihapus.']);
    }

    private function formatUser($u): array
    {
        return [
            'id'         => $u->id,
            'nama'       => $u->nama,
            'email'      => $u->email,
            'no_telp'    => $u->no_telp,
            'role'       => $u->role ? ['id' => $u->role->id, 'kode' => $u->role->kode, 'label' => $u->role->label] : null,
            'status'     => $u->status ? ['id' => $u->status->id, 'kode' => $u->status->kode, 'label' => $u->status->label] : null,
            'wajib_ganti_password' => $u->wajib_ganti_password,
            'created_at' => $u->created_at?->toDateTimeString(),
        ];
    }
}
