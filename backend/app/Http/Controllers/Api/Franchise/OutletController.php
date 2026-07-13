<?php
namespace App\Http\Controllers\Api\Franchise;

use App\Http\Controllers\Controller;
use App\Models\Outlet;
use App\Models\Franchise;
use App\Models\Status;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OutletController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $query = Outlet::with(['franchise.user', 'status']);

        // Franchisee hanya lihat outlet miliknya
        if ($user->hasRole('franchisee')) {
            $franchise = Franchise::where('user_id', $user->id)->first();
            if ($franchise) {
                $query->where('franchise_id', $franchise->id);
            }
        }

        $outlets = $query->orderBy('nama')->paginate(15);

        return response()->json([
            'data' => $outlets->map(fn ($o) => $this->format($o)),
            'meta' => [
                'current_page' => $outlets->currentPage(),
                'last_page' => $outlets->lastPage(),
                'per_page' => $outlets->perPage(),
                'total' => $outlets->total(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'kode_outlet' => 'required|string|max:50|unique:outlets,kode_outlet',
            'alamat' => 'required|string',
            'franchise_id' => 'required|exists:franchises,id',
        ]);

        $aktif = Status::where('konteks', 'outlet')->where('kode', 'aktif')->first();

        $outlet = Outlet::create([
            'nama' => $request->nama,
            'kode_outlet' => $request->kode_outlet,
            'alamat' => $request->alamat,
            'franchise_id' => $request->franchise_id,
            'status_id' => $aktif?->id,
        ]);

        return response()->json([
            'message' => 'Outlet berhasil dibuat.',
            'data' => $this->format($outlet->fresh()->load(['franchise.user', 'status'])),
        ], 201);
    }

    public function show(Outlet $outlet): JsonResponse
    {
        $outlet->load(['franchise.user', 'status', 'users']);
        return response()->json(['data' => $this->format($outlet)]);
    }

    public function update(Request $request, Outlet $outlet): JsonResponse
    {
        $request->validate([
            'nama' => 'sometimes|string|max:255',
            'alamat' => 'sometimes|string',
            'status_id' => 'sometimes|exists:statuses,id',
        ]);

        $outlet->update($request->only(['nama', 'alamat', 'status_id']));

        return response()->json(['message' => 'Outlet berhasil diperbarui.', 'data' => $this->format($outlet->fresh()->load(['franchise.user', 'status']))]);
    }

    private function format($o): array
    {
        return [
            'id' => $o->id,
            'nama' => $o->nama,
            'kode_outlet' => $o->kode_outlet,
            'alamat' => $o->alamat,
            'franchise' => $o->franchise ? [
                'id' => $o->franchise->id,
                'user' => $o->franchise->user ? ['id' => $o->franchise->user->id, 'nama' => $o->franchise->user->nama] : null,
            ] : null,
            'status' => $o->status ? ['id' => $o->status->id, 'kode' => $o->status->kode, 'label' => $o->status->label] : null,
            'created_at' => $o->created_at?->toDateTimeString(),
        ];
    }
}
