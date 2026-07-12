<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Supplier\StoreSupplierRequest;
use App\Http\Requests\Supplier\UpdateSupplierRequest;
use App\Models\Supplier;
use App\Models\Status;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class SupplierController extends Controller
{
    public function index(): JsonResponse
    {
        $suppliers = Supplier::with(['user', 'status'])
            ->with($this->relationForType())
            ->paginate(15);

        return response()->json([
            'data' => $suppliers->map(fn ($s) => $this->formatSupplier($s)),
            'meta' => ['current_page' => $suppliers->currentPage(), 'last_page' => $suppliers->lastPage(), 'per_page' => $suppliers->perPage(), 'total' => $suppliers->total()],
        ]);
    }

    public function store(StoreSupplierRequest $request): JsonResponse
    {
        $data = $request->validated();
        if (empty($data['status_id'])) {
            $aktifStatus = Status::where('konteks', 'supplier')->where('kode', 'aktif')->first();
            $data['status_id'] = $aktifStatus?->id;
        }

        $supplier = DB::transaction(function () use ($request, $data) {
            $supplier = Supplier::create($data);

            if ($supplier->jenis_supplier === 'bahan_baku' && $request->filled('bahan_baku_ids')) {
                $supplier->bahanBakus()->sync($request->bahan_baku_ids);
            }
            if ($supplier->jenis_supplier === 'mesin' && $request->filled('mesin_ids')) {
                $supplier->mesins()->sync($request->mesin_ids);
            }

            return $supplier;
        });

        return response()->json([
            'message' => 'Supplier berhasil dibuat.',
            'data'    => $this->formatSupplier($supplier->load(['user', 'status', $this->relationForType($supplier->jenis_supplier)])),
        ], 201);
    }

    public function show(Supplier $supplier): JsonResponse
    {
        return response()->json([
            'data' => $this->formatSupplier($supplier->load(['user', 'status', $this->relationForType($supplier->jenis_supplier)])),
        ]);
    }

    public function items(Supplier $supplier): JsonResponse
    {
        if ($supplier->jenis_supplier === 'bahan_baku') {
            $items = $supplier->bahanBakus()->get()->map(fn ($b) => [
                'id' => $b->id, 'nama' => $b->nama, 'satuan' => $b->satuan,
                'harga_standar' => (float) $b->harga_standar,
            ]);
        } else {
            $items = $supplier->mesins()->get()->map(fn ($m) => [
                'id' => $m->id, 'nama' => $m->nama, 'kode_mesin' => $m->kode_mesin,
                'harga_standar' => (float) $m->harga_standar,
            ]);
        }

        return response()->json(['data' => $items]);
    }

    public function update(UpdateSupplierRequest $request, Supplier $supplier): JsonResponse
    {
        DB::transaction(function () use ($request, $supplier) {
            $supplier->update($request->validated());

            if ($supplier->jenis_supplier === 'bahan_baku') {
                if ($request->has('bahan_baku_ids')) {
                    $supplier->bahanBakus()->sync($request->bahan_baku_ids);
                }
            } else {
                if ($request->has('mesin_ids')) {
                    $supplier->mesins()->sync($request->mesin_ids);
                }
            }
        });

        return response()->json([
            'message' => 'Supplier berhasil diperbarui.',
            'data'    => $this->formatSupplier($supplier->fresh()->load(['user', 'status', $this->relationForType($supplier->jenis_supplier)])),
        ]);
    }

    public function destroy(Supplier $supplier): JsonResponse
    {
        $supplier->bahanBakus()->detach();
        $supplier->mesins()->detach();
        $supplier->delete();
        return response()->json(['message' => 'Supplier berhasil dihapus.']);
    }

    private function formatSupplier($s): array
    {
        $data = [
            'id' => $s->id,
            'user' => $s->user ? ['id' => $s->user->id, 'nama' => $s->user->nama, 'email' => $s->user->email] : null,
            'jenis_supplier' => $s->jenis_supplier,
            'alamat' => $s->alamat,
            'katalog_produk' => $s->katalog_produk,
            'status' => $s->status ? ['id' => $s->status->id, 'kode' => $s->status->kode, 'label' => $s->status->label] : null,
            'created_at' => $s->created_at?->toDateTimeString(),
        ];

        if ($s->jenis_supplier === 'bahan_baku') {
            $data['bahan_bakus'] = $s->bahanBakus->map(fn ($b) => [
                'id' => $b->id, 'nama' => $b->nama, 'satuan' => $b->satuan, 'harga_standar' => (float) $b->harga_standar,
            ]);
        } else {
            $data['mesins'] = $s->mesins->map(fn ($m) => [
                'id' => $m->id, 'nama' => $m->nama, 'kode_mesin' => $m->kode_mesin, 'merk' => $m->merk,
            ]);
        }

        return $data;
    }

    private function relationForType(?string $jenis = null): string
    {
        return ($jenis ?? request()->input('jenis', 'bahan_baku')) === 'mesin' ? 'mesins' : 'bahanBakus';
    }
}
