<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_po')->unique();
            $table->foreignId('supplier_id')->constrained('suppliers')->cascadeOnDelete();
            $table->foreignId('dibuat_oleh_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('jenis_po', ['bahan_baku', 'mesin']);
            $table->decimal('total_nilai', 14, 2)->default(0);
            $table->foreignId('status_id')->nullable()->constrained('statuses')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('purchase_order_item_bahan_bakus', function (Blueprint $table) {
            $table->id();
            $table->foreignId('po_id')->constrained('purchase_orders')->cascadeOnDelete();
            $table->foreignId('bahan_baku_id')->constrained('bahan_bakus')->cascadeOnDelete();
            $table->decimal('jumlah', 12, 4);
            $table->decimal('harga_satuan', 12, 2);
            $table->decimal('qty_disetujui', 12, 4)->nullable();
            $table->foreignId('status_id')->nullable()->constrained('statuses')->nullOnDelete();
            $table->text('alasan')->nullable();
            $table->timestamps();
            $table->unique(['po_id', 'bahan_baku_id']);
        });

        Schema::create('purchase_order_item_mesins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('po_id')->constrained('purchase_orders')->cascadeOnDelete();
            $table->foreignId('mesin_id')->constrained('mesins')->cascadeOnDelete();
            $table->integer('jumlah');
            $table->decimal('harga_satuan', 12, 2);
            $table->integer('qty_disetujui')->nullable();
            $table->foreignId('status_id')->nullable()->constrained('statuses')->nullOnDelete();
            $table->text('alasan')->nullable();
            $table->timestamps();
            $table->unique(['po_id', 'mesin_id']);
        });
    }
    public function down(): void {
        Schema::dropIfExists('purchase_order_item_mesins');
        Schema::dropIfExists('purchase_order_item_bahan_bakus');
        Schema::dropIfExists('purchase_orders');
    }
};
