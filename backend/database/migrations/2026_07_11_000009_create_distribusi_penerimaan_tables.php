<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('distribusi_barangs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('po_id')->constrained('purchase_orders')->cascadeOnDelete();
            $table->string('nomor_distribusi')->unique();
            $table->date('tanggal_kirim');
            $table->foreignId('status_id')->nullable()->constrained('statuses')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('dist_detail_bahan_bakus', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('distribusi_barang_id');
            $table->foreign('distribusi_barang_id', 'ddb_dist_fk')->references('id')->on('distribusi_barangs')->cascadeOnDelete();
            $table->unsignedBigInteger('po_item_id');
            $table->foreign('po_item_id', 'ddb_poitem_fk')->references('id')->on('purchase_order_item_bahan_bakus')->cascadeOnDelete();
            $table->decimal('jumlah_kirim', 12, 4);
            $table->timestamps();
        });

        Schema::create('dist_detail_mesins', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('distribusi_barang_id');
            $table->foreign('distribusi_barang_id', 'ddm_dist_fk')->references('id')->on('distribusi_barangs')->cascadeOnDelete();
            $table->unsignedBigInteger('po_item_id');
            $table->foreign('po_item_id', 'ddm_poitem_fk')->references('id')->on('purchase_order_item_mesins')->cascadeOnDelete();
            $table->integer('jumlah_kirim');
            $table->timestamps();
        });

        Schema::create('penerimaan_barangs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('distribusi_barang_id')->constrained('distribusi_barangs')->cascadeOnDelete();
            $table->string('nomor_penerimaan')->unique();
            $table->date('tanggal_terima');
            $table->decimal('subtotal', 14, 2)->default(0);
            $table->decimal('diskon', 12, 2)->default(0);
            $table->decimal('ppn', 12, 2)->default(0);
            $table->decimal('total_bayar', 14, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('penerimaan_detail_bahan_bakus', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('penerimaan_barang_id');
            $table->foreign('penerimaan_barang_id', 'pdb_penerimaan_fk')->references('id')->on('penerimaan_barangs')->cascadeOnDelete();
            $table->unsignedBigInteger('distribusi_detail_id');
            $table->foreign('distribusi_detail_id', 'pdb_dist_fk')->references('id')->on('dist_detail_bahan_bakus')->cascadeOnDelete();
            $table->decimal('qty_diterima', 12, 4);
            $table->string('kondisi')->default('baik');
            $table->timestamps();
        });

        Schema::create('penerimaan_detail_mesins', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('penerimaan_barang_id');
            $table->foreign('penerimaan_barang_id', 'pdm_penerimaan_fk')->references('id')->on('penerimaan_barangs')->cascadeOnDelete();
            $table->unsignedBigInteger('distribusi_detail_id');
            $table->foreign('distribusi_detail_id', 'pdm_dist_fk')->references('id')->on('dist_detail_mesins')->cascadeOnDelete();
            $table->string('nomor_seri')->nullable();
            $table->integer('qty_diterima');
            $table->string('kondisi')->default('baik');
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('penerimaan_detail_mesins');
        Schema::dropIfExists('penerimaan_detail_bahan_bakus');
        Schema::dropIfExists('penerimaan_barangs');
        Schema::dropIfExists('dist_detail_mesins');
        Schema::dropIfExists('dist_detail_bahan_bakus');
        Schema::dropIfExists('distribusi_barangs');
    }
};
