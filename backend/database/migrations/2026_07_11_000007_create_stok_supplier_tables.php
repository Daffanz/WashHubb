<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stok_supplier_bahan_bakus', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')->constrained('suppliers')->cascadeOnDelete();
            $table->foreignId('bahan_baku_id')->constrained('bahan_bakus')->cascadeOnDelete();
            $table->decimal('stok_saat_ini', 12, 4)->default(0);
            $table->foreignId('status_id')->nullable()->constrained('statuses')->nullOnDelete();
            $table->timestamps();
            $table->unique(['supplier_id', 'bahan_baku_id']);
        });

        Schema::create('mutasi_stok_supplier_bahan_bakus', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('stok_supplier_bahan_baku_id');
            $table->foreign('stok_supplier_bahan_baku_id', 'ms_sbb_fk')->references('id')->on('stok_supplier_bahan_bakus')->cascadeOnDelete();
            $table->string('jenis_mutasi');
            $table->decimal('jumlah', 12, 4);
            $table->timestamp('tanggal');
            $table->timestamps();
        });

        Schema::create('stok_supplier_mesins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')->constrained('suppliers')->cascadeOnDelete();
            $table->foreignId('mesin_id')->constrained('mesins')->cascadeOnDelete();
            $table->integer('stok_saat_ini')->default(0);
            $table->foreignId('status_id')->nullable()->constrained('statuses')->nullOnDelete();
            $table->timestamps();
            $table->unique(['supplier_id', 'mesin_id']);
        });

        Schema::create('mutasi_stok_supplier_mesins', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('stok_supplier_mesin_id');
            $table->foreign('stok_supplier_mesin_id', 'ms_sm_fk')->references('id')->on('stok_supplier_mesins')->cascadeOnDelete();
            $table->string('jenis_mutasi');
            $table->integer('jumlah');
            $table->timestamp('tanggal');
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('mutasi_stok_supplier_mesins');
        Schema::dropIfExists('stok_supplier_mesins');
        Schema::dropIfExists('mutasi_stok_supplier_bahan_bakus');
        Schema::dropIfExists('stok_supplier_bahan_bakus');
    }
};
