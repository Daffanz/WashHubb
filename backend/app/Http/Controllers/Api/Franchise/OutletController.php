<?php
namespace App\Http\Controllers\Api\Franchise;

use App\Http\Controllers\Controller;
use App\Models\Outlet;
use App\Models\Franchise;
use App\Models\ManajerOperasional;
use App\Models\Status;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OutletController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $query = Outlet::with(['franchise.user', 'managerOutlet', 'status']);

        // Franchisee hanya lihat outlet miliknya
        if ($user->hasRole('franchisee') || $user->hasRole('franchise')) {
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
            'manager_outlet_id' => 'nullable|exists:users,id',
        ]);

        $aktif = Status::where('konteks', 'outlet')->where('kode', 'aktif')->first();

        $outlet = Outlet::create([
            'nama' => $request->nama,
            'kode_outlet' => $request->kode_outlet,
            'alamat' => $request->alamat,
            'franchise_id' => $request->franchise_id,
            'manager_outlet_id' => $request->manager_outlet_id,
            'status_id' => $aktif?->id,
        ]);

        // Sync franchise user to user_outlets
        $franchise = Franchise::find($request->franchise_id);
        if ($franchise && $franchise->user_id) {
            $outlet->users()->syncWithoutDetaching([$franchise->user_id]);
        }

        // Sync manager outlet user to user_outlets
        if ($request->manager_outlet_id) {
            $outlet->users()->syncWithoutDetaching([$request->manager_outlet_id]);
        }

        return response()->json([
            'message' => 'Outlet berhasil dibuat.',
            'data' => $this->format($outlet->fresh()->load(['franchise.user', 'managerOutlet', 'status'])),
        ], 201);
    }

    public function show(Outlet $outlet): JsonResponse
    {
        $outlet->load(['franchise.user', 'managerOutlet', 'status', 'users', 'stokBahanBakus.bahanBaku', 'stokMesins.mesin']);
        return response()->json(['data' => $this->format($outlet)]);
    }

    public function franchises(): JsonResponse
    {
        $franchises = Franchise::with('user')->get()->map(fn ($f) => [
            'id' => $f->id,
            'user' => $f->user ? ['id' => $f->user->id, 'nama' => $f->user->nama, 'email' => $f->user->email] : null,
        ]);

        return response()->json(['data' => $franchises]);
    }

    public function manajerOperasionals(): JsonResponse
    {
        $managers = ManajerOperasional::with('user')->get()->map(fn ($m) => [
            'id' => $m->id,
            'user_id' => $m->user_id,
            'user' => $m->user ? ['id' => $m->user->id, 'nama' => $m->user->nama, 'email' => $m->user->email] : null,
        ]);

        return response()->json(['data' => $managers]);
    }

    public function update(Request $request, Outlet $outlet): JsonResponse
    {
        $request->validate([
            'nama' => 'sometimes|string|max:255',
            'alamat' => 'sometimes|string',
            'franchise_id' => 'sometimes|exists:franchises,id',
            'manager_outlet_id' => 'nullable|exists:users,id',
            'status_id' => 'sometimes|exists:statuses,id',
        ]);

        $outlet->update($request->only(['nama', 'alamat', 'franchise_id', 'manager_outlet_id', 'status_id']));

        // Sync franchise user to user_outlets if franchise changed
        if ($request->has('franchise_id')) {
            $franchise = Franchise::find($request->franchise_id);
            if ($franchise && $franchise->user_id) {
                $outlet->users()->syncWithoutDetaching([$franchise->user_id]);
            }
        }

        // Sync manager outlet user to user_outlets if changed
        if ($request->has('manager_outlet_id') && $request->manager_outlet_id) {
            $outlet->users()->syncWithoutDetaching([$request->manager_outlet_id]);
        }

        return response()->json(['message' => 'Outlet berhasil diperbarui.', 'data' => $this->format($outlet->fresh()->load(['franchise.user', 'managerOutlet', 'status']))]);
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
            'manager_outlet' => $o->managerOutlet ? [
                'id' => $o->managerOutlet->id,
                'nama' => $o->managerOutlet->nama,
            ] : null,
            'status' => $o->status ? ['id' => $o->status->id, 'kode' => $o->status->kode, 'label' => $o->status->label] : null,
            'users' => $o->users->map(fn ($u) => ['id' => $u->id, 'nama' => $u->nama]),
            'stok_bahan_bakus' => $o->stokBahanBakus->map(fn ($s) => [
                'id' => $s->id,
                'bahan_baku' => $s->bahanBaku ? ['id' => $s->bahanBaku->id, 'nama' => $s->bahanBaku->nama] : null,
                'stok_saat_ini' => (float) $s->stok_saat_ini,
            ]),
            'stok_mesins' => $o->stokMesins->map(fn ($s) => [
                'id' => $s->id,
                'mesin' => $s->mesin ? ['id' => $s->mesin->id, 'nama' => $s->mesin->nama] : null,
                'stok_saat_ini' => (int) $s->stok_saat_ini,
            ]),
            'created_at' => $o->created_at?->toDateTimeString(),
        ];
    }
}
