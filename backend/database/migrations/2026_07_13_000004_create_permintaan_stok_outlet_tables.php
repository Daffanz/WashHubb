<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('permintaan_stok_outlets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('outlet_id', 'pso_outlet_fk')->constrained('outlets')->cascadeOnDelete();
            $table->foreignId('user_id', 'pso_user_fk')->constrained('users')->cascadeOnDelete();
            $table->date('tanggal');
            $table->foreignId('status_id')->nullable()->constrained('statuses')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('permintaan_stok_outlet_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('permintaan_stok_outlet_id', 'psod_permintaan_fk')->constrained('permintaan_stok_outlets')->cascadeOnDelete();
            $table->foreignId('bahan_baku_id', 'psod_bahan_fk')->constrained('bahan_bakus')->cascadeOnDelete();
            $table->decimal('jumlah_diminta', 12, 4);
            $table->decimal('jumlah_disetujui', 12, 4)->nullable();
            $table->text('alasan')->nullable();
            $table->foreignId('status_id')->nullable()->constrained('statuses')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permintaan_stok_outlet_details');
        Schema::dropIfExists('permintaan_stok_outlets');
    }
};
