<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kategori_bahan_bakus', function (Blueprint $table) {
            $table->id();
            $table->string('nama')->unique();
            $table->timestamps();
        });

        Schema::create('bahan_bakus', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->foreignId('kategori_id')->constrained('kategori_bahan_bakus')->cascadeOnDelete();
            $table->string('satuan');
            $table->decimal('harga_standar', 12, 2);
            $table->foreignId('status_id')->nullable()->constrained('statuses')->nullOnDelete();
            $table->timestamps();
            $table->unique(['nama', 'kategori_id']);
        });

        Schema::create('jenis_layanans', function (Blueprint $table) {
            $table->id();
            $table->string('nama')->unique();
            $table->decimal('harga_standar_per_kg', 12, 2);
            $table->foreignId('status_id')->nullable()->constrained('statuses')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('jenis_layanan_bahan_bakus', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jenis_layanan_id')->constrained('jenis_layanans')->cascadeOnDelete();
            $table->foreignId('bahan_baku_id')->constrained('bahan_bakus')->cascadeOnDelete();
            $table->decimal('jumlah_konsumsi', 10, 4);
            $table->timestamps();
            $table->unique(['jenis_layanan_id', 'bahan_baku_id']);
        });

        Schema::create('mesins', function (Blueprint $table) {
            $table->id();
            $table->string('nama')->unique();
            $table->string('kode_mesin')->unique();
            $table->string('merk')->nullable();
            $table->string('tipe')->nullable();
            $table->integer('kapasitas')->nullable();
            $table->foreignId('status_id')->nullable()->constrained('statuses')->nullOnDelete();
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('mesins');
        Schema::dropIfExists('jenis_layanan_bahan_bakus');
        Schema::dropIfExists('jenis_layanans');
        Schema::dropIfExists('bahan_bakus');
        Schema::dropIfExists('kategori_bahan_bakus');
    }
};
