<?php
namespace Database\Seeders;

use App\Models\User;
use App\Models\Status;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Roles
        $roles = [
            ['kode' => 'admin_it', 'label' => 'Admin IT'],
            ['kode' => 'franchisor', 'label' => 'Franchisor'],
            ['kode' => 'procurement', 'label' => 'Tim Pengadaan'],
            ['kode' => 'supplier', 'label' => 'Supplier'],
            ['kode' => 'manager_outlet', 'label' => 'Manager Outlet'],
            ['kode' => 'manajer_operasional', 'label' => 'Manajer Operasional'],
        ];

        foreach ($roles as $r) {
            Role::firstOrCreate(['kode' => $r['kode']], ['label' => $r['label']]);
        }

        // Permissions
        $permissions = [
            ['kode' => 'user-list', 'nama' => 'Lihat User', 'modul' => 'akun'],
            ['kode' => 'user-create', 'nama' => 'Buat User', 'modul' => 'akun'],
            ['kode' => 'user-edit', 'nama' => 'Edit User', 'modul' => 'akun'],
            ['kode' => 'user-delete', 'nama' => 'Hapus User', 'modul' => 'akun'],
            ['kode' => 'role-list', 'nama' => 'Lihat Role', 'modul' => 'akun'],
            ['kode' => 'role-create', 'nama' => 'Buat Role', 'modul' => 'akun'],
            ['kode' => 'role-edit', 'nama' => 'Edit Role', 'modul' => 'akun'],
            ['kode' => 'role-delete', 'nama' => 'Hapus Role', 'modul' => 'akun'],
            ['kode' => 'permission-list', 'nama' => 'Lihat Permission', 'modul' => 'akun'],
            ['kode' => 'supplier-list', 'nama' => 'Lihat Supplier', 'modul' => 'supplier'],
            ['kode' => 'supplier-create', 'nama' => 'Buat Supplier', 'modul' => 'supplier'],
            ['kode' => 'supplier-edit', 'nama' => 'Edit Supplier', 'modul' => 'supplier'],
            ['kode' => 'supplier-delete', 'nama' => 'Hapus Supplier', 'modul' => 'supplier'],
            ['kode' => 'category-list', 'nama' => 'Lihat Kategori', 'modul' => 'master'],
            ['kode' => 'category-create', 'nama' => 'Buat Kategori', 'modul' => 'master'],
            ['kode' => 'category-edit', 'nama' => 'Edit Kategori', 'modul' => 'master'],
            ['kode' => 'category-delete', 'nama' => 'Hapus Kategori', 'modul' => 'master'],
            ['kode' => 'material-list', 'nama' => 'Lihat Bahan Baku', 'modul' => 'master'],
            ['kode' => 'material-create', 'nama' => 'Buat Bahan Baku', 'modul' => 'master'],
            ['kode' => 'material-edit', 'nama' => 'Edit Bahan Baku', 'modul' => 'master'],
            ['kode' => 'material-delete', 'nama' => 'Hapus Bahan Baku', 'modul' => 'master'],
            ['kode' => 'service-list', 'nama' => 'Lihat Layanan', 'modul' => 'master'],
            ['kode' => 'service-create', 'nama' => 'Buat Layanan', 'modul' => 'master'],
            ['kode' => 'service-edit', 'nama' => 'Edit Layanan', 'modul' => 'master'],
            ['kode' => 'service-delete', 'nama' => 'Hapus Layanan', 'modul' => 'master'],
            ['kode' => 'machine-list', 'nama' => 'Lihat Mesin', 'modul' => 'master'],
            ['kode' => 'machine-create', 'nama' => 'Buat Mesin', 'modul' => 'master'],
            ['kode' => 'machine-edit', 'nama' => 'Edit Mesin', 'modul' => 'master'],
            ['kode' => 'machine-delete', 'nama' => 'Hapus Mesin', 'modul' => 'master'],
            ['kode' => 'po-list', 'nama' => 'Lihat PO', 'modul' => 'pengadaan'],
            ['kode' => 'po-create', 'nama' => 'Buat PO', 'modul' => 'pengadaan'],
            ['kode' => 'po-edit', 'nama' => 'Edit PO', 'modul' => 'pengadaan'],
            ['kode' => 'po-delete', 'nama' => 'Hapus PO', 'modul' => 'pengadaan'],
            ['kode' => 'po-validate', 'nama' => 'Validasi PO', 'modul' => 'pengadaan'],
            ['kode' => 'po-kirim', 'nama' => 'Kirim PO', 'modul' => 'pengadaan'],
            ['kode' => 'distribution-list', 'nama' => 'Lihat Distribusi', 'modul' => 'pengadaan'],
            ['kode' => 'distribution-create', 'nama' => 'Buat Distribusi', 'modul' => 'pengadaan'],
            ['kode' => 'receipt-list', 'nama' => 'Lihat Penerimaan', 'modul' => 'pengadaan'],
            ['kode' => 'receipt-create', 'nama' => 'Buat Penerimaan', 'modul' => 'pengadaan'],
            ['kode' => 'retur-list', 'nama' => 'Lihat Retur', 'modul' => 'pengadaan'],
            ['kode' => 'retur-create', 'nama' => 'Buat Retur', 'modul' => 'pengadaan'],
            ['kode' => 'retur-validate', 'nama' => 'Validasi Retur', 'modul' => 'pengadaan'],
            ['kode' => 'stock-list', 'nama' => 'Lihat Stok', 'modul' => 'inventory'],
            ['kode' => 'stock-view', 'nama' => 'Detail Stok', 'modul' => 'inventory'],
            ['kode' => 'stock-manage', 'nama' => 'Kelola Stok', 'modul' => 'inventory'],
            ['kode' => 'mutation-list', 'nama' => 'Lihat Mutasi', 'modul' => 'inventory'],
            ['kode' => 'mutation-create', 'nama' => 'Buat Mutasi', 'modul' => 'inventory'],
            ['kode' => 'outlet-list', 'nama' => 'Lihat Outlet', 'modul' => 'outlet'],
            ['kode' => 'outlet-create', 'nama' => 'Buat Outlet', 'modul' => 'outlet'],
            ['kode' => 'outlet-edit', 'nama' => 'Edit Outlet', 'modul' => 'outlet'],
            ['kode' => 'outlet-delete', 'nama' => 'Hapus Outlet', 'modul' => 'outlet'],
        ];

        foreach ($permissions as $p) {
            Permission::firstOrCreate(['kode' => $p['kode']], $p);
        }

        // Assign permissions to roles
        $adminIt = Role::where('kode', 'admin_it')->first();
        $adminIt->permissions()->sync(Permission::pluck('id'));

        $franchisor = Role::where('kode', 'franchisor')->first();
        $franchisor->permissions()->sync(Permission::whereIn('kode', [
            'category-list', 'category-create', 'category-edit', 'category-delete',
            'material-list', 'material-create', 'material-edit', 'material-delete',
            'service-list', 'service-create', 'service-edit', 'service-delete',
            'machine-list', 'machine-create', 'machine-edit', 'machine-delete',
        ])->pluck('id'));

        $procurement = Role::where('kode', 'procurement')->first();
        $procurement->permissions()->sync(Permission::whereIn('kode', [
            'supplier-list', 'supplier-create', 'supplier-edit',
            'po-list', 'po-create', 'po-edit', 'po-delete', 'po-kirim',
            'distribution-list', 'distribution-create',
            'receipt-list', 'receipt-create',
            'retur-list', 'retur-create', 'retur-validate',
            'stock-list', 'stock-view',
            'mutation-list', 'mutation-create',
        ])->pluck('id'));

        $supplier = Role::where('kode', 'supplier')->first();
        $supplier->permissions()->sync(Permission::whereIn('kode', [
            'po-list', 'po-validate',
            'distribution-list', 'distribution-create',
            'stock-list', 'stock-view', 'stock-manage',
            'mutation-list', 'mutation-create',
        ])->pluck('id'));

        $managerOutlet = Role::where('kode', 'manager_outlet')->first();
        $managerOutlet->permissions()->sync(Permission::whereIn('kode', [
            'stock-list', 'stock-view',
            'mutation-list', 'mutation-create',
        ])->pluck('id'));

        // Create admin user
        $adminRole = Role::where('kode', 'admin_it')->first();
        $aktifStatus = Status::where('konteks', 'akun')->where('kode', 'aktif')->first();

        User::firstOrCreate(
            ['email' => 'admin@washhub.com'],
            [
                'nama' => 'Admin IT',
                'password' => Hash::make('password'),
                'no_telp' => '081234567890',
                'role_id' => $adminRole->id,
                'status_id' => $aktifStatus->id,
            ]
        );
    }
}
