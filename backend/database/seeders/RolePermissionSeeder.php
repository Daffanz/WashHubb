<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Status;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            // User management
            'user-list', 'user-create', 'user-edit', 'user-delete',
            // Role management
            'role-list', 'role-create', 'role-edit', 'role-delete',
            // Permission management
            'permission-list',
            // Supplier
            'supplier-list', 'supplier-create', 'supplier-edit', 'supplier-delete',
            // Master data
            'category-list', 'category-create', 'category-edit', 'category-delete',
            'material-list', 'material-create', 'material-edit', 'material-delete',
            'service-list', 'service-create', 'service-edit', 'service-delete',
            'machine-list', 'machine-create', 'machine-edit', 'machine-delete',
            // Procurement
            'po-list', 'po-create', 'po-edit', 'po-delete', 'po-validate',
            'distribution-list', 'distribution-create',
            'receipt-list', 'receipt-create',
            // Inventory
            'stock-list', 'stock-view',
            'mutation-list', 'mutation-create',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        // admin_it - full user/role/permission management
        $adminIt = Role::firstOrCreate(['name' => 'admin_it', 'guard_name' => 'web']);
        $adminIt->syncPermissions([
            'user-list', 'user-create', 'user-edit', 'user-delete',
            'role-list', 'role-create', 'role-edit', 'role-delete',
            'permission-list',
            'supplier-list', 'supplier-create', 'supplier-edit', 'supplier-delete',
        ]);

        // franchisor - master data
        $franchisor = Role::firstOrCreate(['name' => 'franchisor', 'guard_name' => 'web']);
        $franchisor->syncPermissions([
            'category-list', 'category-create', 'category-edit', 'category-delete',
            'material-list', 'material-create', 'material-edit', 'material-delete',
            'service-list', 'service-create', 'service-edit', 'service-delete',
            'machine-list', 'machine-create', 'machine-edit', 'machine-delete',
        ]);

        // procurement - supplier & PO
        $procurement = Role::firstOrCreate(['name' => 'procurement', 'guard_name' => 'web']);
        $procurement->syncPermissions([
            'supplier-list', 'supplier-create', 'supplier-edit',
            'po-list', 'po-create', 'po-edit',
            'distribution-list', 'distribution-create',
            'receipt-list', 'receipt-create',
        ]);

        // supplier - validasi PO
        $supplier = Role::firstOrCreate(['name' => 'supplier', 'guard_name' => 'web']);
        $supplier->syncPermissions(['po-list', 'po-validate']);

        // manager_outlet - inventory
        $managerOutlet = Role::firstOrCreate(['name' => 'manager_outlet', 'guard_name' => 'web']);
        $managerOutlet->syncPermissions([
            'stock-list', 'stock-view',
            'mutation-list', 'mutation-create',
        ]);

        // Create default admin_it user
        $activeStatus = Status::where('group', 'user')->where('name', 'aktif')->first();

        $admin = User::firstOrCreate(
            ['email' => 'admin@washhub.com'],
            [
                'name' => 'Admin IT',
                'password' => Hash::make('password'),
                'status_id' => $activeStatus?->id,
            ]
        );
        $admin->assignRole('admin_it');
    }
}
