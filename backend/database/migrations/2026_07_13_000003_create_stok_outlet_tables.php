<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stok_outlet_bahan_bakus', function (Blueprint $table) {
            $table->id();
            $table->foreignId('outlet_id', 'sob_outlet_fk')->constrained('outlets')->cascadeOnDelete();
            $table->foreignId('bahan_baku_id', 'sob_bahan_fk')->constrained('bahan_bakus')->cascadeOnDelete();
            $table->decimal('stok_saat_ini', 12, 4)->default(0);
            $table->decimal('stok_minimum', 12, 4)->default(0);
            $table->decimal('stok_masuk', 12, 4)->default(0);
            $table->decimal('stok_keluar', 12, 4)->default(0);
            $table->timestamps();
            $table->unique(['outlet_id', 'bahan_baku_id']);
        });

        Schema::create('mutasi_stok_outlet_bahan_bakus', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stok_outlet_bahan_baku_id', 'mso_stok_fk')->constrained('stok_outlet_bahan_bakus')->cascadeOnDelete();
            $table->string('jenis_mutasi');
            $table->decimal('jumlah', 12, 4);
            $table->timestamp('tanggal');
            $table->unsignedBigInteger('order_cucian_id')->nullable();
            $table->foreign('order_cucian_id', 'mso_order_fk')->references('id')->on('order_cucians')->nullOnDelete();
            $table->unsignedBigInteger('penerimaan_outlet_detail_id')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::table('mutasi_stok_outlet_bahan_bakus', function (Blueprint $table) {
            $table->dropForeign('mso_stok_fk');
            $table->dropForeign('mso_order_fk');
        });
        Schema::dropIfExists('mutasi_stok_outlet_bahan_bakus');
        Schema::dropIfExists('stok_outlet_bahan_bakus');
    }
};
