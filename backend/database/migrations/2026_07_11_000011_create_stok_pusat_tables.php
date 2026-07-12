<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stok_pusat_bahan_bakus', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bahan_baku_id')->constrained('bahan_bakus')->cascadeOnDelete();
            $table->decimal('stok_masuk', 12, 4)->default(0);
            $table->decimal('stok_keluar', 12, 4)->default(0);
            $table->decimal('stok_saat_ini', 12, 4)->default(0);
            $table->timestamps();
            $table->unique('bahan_baku_id');
        });

        Schema::create('mutasi_stok_pusat_bahan_bakus', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('stok_pusat_bahan_baku_id');
            $table->foreign('stok_pusat_bahan_baku_id', 'mspb_stok_fk')->references('id')->on('stok_pusat_bahan_bakus')->cascadeOnDelete();
            $table->string('jenis_mutasi');
            $table->decimal('jumlah', 12, 4);
            $table->timestamp('tanggal');
            $table->unsignedBigInteger('penerimaan_detail_id')->nullable();
            $table->foreign('penerimaan_detail_id', 'mspb_penerimaan_fk')->references('id')->on('penerimaan_detail_bahan_bakus')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('stok_pusat_mesins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mesin_id')->constrained('mesins')->cascadeOnDelete();
            $table->integer('stok_masuk')->default(0);
            $table->integer('stok_keluar')->default(0);
            $table->integer('stok_saat_ini')->default(0);
            $table->timestamps();
            $table->unique('mesin_id');
        });

        Schema::create('mutasi_stok_pusat_mesins', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('stok_pusat_mesin_id');
            $table->foreign('stok_pusat_mesin_id', 'mspm_stok_fk')->references('id')->on('stok_pusat_mesins')->cascadeOnDelete();
            $table->string('jenis_mutasi');
            $table->integer('jumlah');
            $table->timestamp('tanggal');
            $table->unsignedBigInteger('penerimaan_detail_id')->nullable();
            $table->foreign('penerimaan_detail_id', 'mspm_penerimaan_fk')->references('id')->on('penerimaan_detail_mesins')->nullOnDelete();
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('mutasi_stok_pusat_mesins');
        Schema::dropIfExists('stok_pusat_mesins');
        Schema::dropIfExists('mutasi_stok_pusat_bahan_bakus');
        Schema::dropIfExists('stok_pusat_bahan_bakus');
    }
};
