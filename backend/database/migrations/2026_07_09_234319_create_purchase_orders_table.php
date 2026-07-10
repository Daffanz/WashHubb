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
            $table->enum('jenis_po', ['bahan_baku', 'mesin']);
            $table->enum('status_validasi', ['pending', 'disetujui', 'ditolak'])->default('pending');
            $table->foreignId('status_id')->nullable()->constrained('statuses')->nullOnDelete();
            $table->text('catatan')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_orders');
    }
};
