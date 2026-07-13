<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('distribusi_outlets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('permintaan_stok_outlet_id', 'do_permintaan_fk')->constrained('permintaan_stok_outlets')->cascadeOnDelete();
            $table->date('tanggal_kirim');
            $table->foreignId('status_id')->nullable()->constrained('statuses')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('distribusi_outlet_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('distribusi_outlet_id', 'dod_dist_fk')->constrained('distribusi_outlets')->cascadeOnDelete();
            $table->foreignId('bahan_baku_id', 'dod_bahan_fk')->constrained('bahan_bakus')->cascadeOnDelete();
            $table->decimal('jumlah_kirim', 12, 4);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('distribusi_outlet_details');
        Schema::dropIfExists('distribusi_outlets');
    }
};
