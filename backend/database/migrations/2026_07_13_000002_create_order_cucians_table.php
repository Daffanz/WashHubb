<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_cucians', function (Blueprint $table) {
            $table->id();
            $table->foreignId('outlet_id', 'oc_outlet_fk')->constrained('outlets')->cascadeOnDelete();
            $table->foreignId('user_id', 'oc_user_fk')->constrained('users')->cascadeOnDelete();
            $table->foreignId('jenis_layanan_id', 'oc_layanan_fk')->constrained('jenis_layanans')->cascadeOnDelete();
            $table->foreignId('detail_mesin_id', 'oc_mesin_fk')->constrained('detail_mesins')->cascadeOnDelete();
            $table->decimal('berat', 8, 2);
            $table->decimal('total_harga', 14, 2)->default(0);
            $table->timestamp('waktu_masuk');
            $table->timestamp('estimasi_selesai')->nullable();
            $table->timestamp('waktu_selesai')->nullable();
            $table->text('alasan_pembatalan')->nullable();
            $table->foreignId('status_id')->nullable()->constrained('statuses')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_cucians');
    }
};
