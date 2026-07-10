<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('penerimaan_barangs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('distribusi_barang_id')->constrained('distribusi_barangs')->cascadeOnDelete();
            $table->date('tanggal_terima');
            $table->decimal('total_bayar', 14, 2)->default(0);
            $table->text('catatan')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('penerimaan_barangs');
    }
};
