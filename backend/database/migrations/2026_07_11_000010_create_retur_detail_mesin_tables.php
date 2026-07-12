<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('retur_barangs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('penerimaan_barang_id')->constrained('penerimaan_barangs')->cascadeOnDelete();
            $table->timestamp('tanggal_retur');
            $table->foreignId('status_id')->nullable()->constrained('statuses')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('retur_detail_bahan_bakus', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('retur_barang_id');
            $table->foreign('retur_barang_id', 'rdb_retur_fk')->references('id')->on('retur_barangs')->cascadeOnDelete();
            $table->unsignedBigInteger('penerimaan_detail_id');
            $table->foreign('penerimaan_detail_id', 'rdb_penerimaan_fk')->references('id')->on('penerimaan_detail_bahan_bakus')->cascadeOnDelete();
            $table->decimal('qty_retur', 12, 4);
            $table->text('alasan');
            $table->string('foto_bukti')->nullable();
            $table->decimal('qty_pengganti', 12, 4)->nullable();
            $table->foreignId('status_id')->nullable()->constrained('statuses')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('retur_detail_mesins', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('retur_barang_id');
            $table->foreign('retur_barang_id', 'rdm_retur_fk')->references('id')->on('retur_barangs')->cascadeOnDelete();
            $table->unsignedBigInteger('penerimaan_detail_id');
            $table->foreign('penerimaan_detail_id', 'rdm_penerimaan_fk')->references('id')->on('penerimaan_detail_mesins')->cascadeOnDelete();
            $table->integer('qty_retur');
            $table->text('alasan');
            $table->string('foto_bukti')->nullable();
            $table->integer('qty_pengganti')->nullable();
            $table->string('nomor_seri_pengganti')->nullable();
            $table->foreignId('status_id')->nullable()->constrained('statuses')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('detail_mesins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mesin_id')->constrained('mesins')->cascadeOnDelete();
            $table->string('nomor_seri')->unique();
            $table->foreignId('outlet_id')->nullable()->constrained('outlets')->nullOnDelete();
            $table->date('tanggal_terima_pusat')->nullable();
            $table->foreignId('status_id')->nullable()->constrained('statuses')->nullOnDelete();
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('detail_mesins');
        Schema::dropIfExists('retur_detail_mesins');
        Schema::dropIfExists('retur_detail_bahan_bakus');
        Schema::dropIfExists('retur_barangs');
    }
};
