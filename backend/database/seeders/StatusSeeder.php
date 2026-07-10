<?php

namespace Database\Seeders;

use App\Models\Status;
use Illuminate\Database\Seeder;

class StatusSeeder extends Seeder
{
    public function run(): void
    {
        $statuses = [
            // User statuses
            ['group' => 'user', 'name' => 'aktif',     'description' => 'User aktif dan dapat login'],
            ['group' => 'user', 'name' => 'nonaktif',  'description' => 'User nonaktif, tidak dapat login'],

            // Supplier statuses
            ['group' => 'supplier', 'name' => 'aktif',    'description' => 'Supplier aktif'],
            ['group' => 'supplier', 'name' => 'nonaktif', 'description' => 'Supplier nonaktif'],

            // Purchase Order statuses
            ['group' => 'purchase_order', 'name' => 'draft',     'description' => 'PO masih draft'],
            ['group' => 'purchase_order', 'name' => 'dikirim',   'description' => 'PO sudah dikirim ke supplier'],
            ['group' => 'purchase_order', 'name' => 'disetujui', 'description' => 'PO disetujui supplier'],
            ['group' => 'purchase_order', 'name' => 'ditolak',   'description' => 'PO ditolak supplier'],
            ['group' => 'purchase_order', 'name' => 'selesai',   'description' => 'PO selesai diproses'],

            // Distribusi statuses
            ['group' => 'distribusi', 'name' => 'diproses', 'description' => 'Distribusi sedang diproses'],
            ['group' => 'distribusi', 'name' => 'dikirim',  'description' => 'Barang sedang dikirim'],
            ['group' => 'distribusi', 'name' => 'diterima', 'description' => 'Barang sudah diterima'],
        ];

        foreach ($statuses as $status) {
            Status::firstOrCreate(
                ['group' => $status['group'], 'name' => $status['name']],
                ['description' => $status['description']]
            );
        }
    }
}
