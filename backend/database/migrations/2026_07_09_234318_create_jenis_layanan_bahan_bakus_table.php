<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jenis_layanan_bahan_bakus', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jenis_layanan_id')->constrained('jenis_layanans')->cascadeOnDelete();
            $table->foreignId('bahan_baku_id')->constrained('bahan_bakus')->cascadeOnDelete();
            $table->decimal('jumlah_konsumsi', 10, 4);
            $table->timestamps();

            $table->unique(['jenis_layanan_id', 'bahan_baku_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jenis_layanan_bahan_bakus');
    }
};
