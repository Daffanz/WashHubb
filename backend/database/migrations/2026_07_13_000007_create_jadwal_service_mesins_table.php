<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jadwal_service_mesins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('detail_mesin_id', 'jsm_mesin_fk')->constrained('detail_mesins')->cascadeOnDelete();
            $table->foreignId('outlet_id', 'jsm_outlet_fk')->constrained('outlets')->cascadeOnDelete();
            $table->foreignId('user_id', 'jsm_user_fk')->constrained('users')->cascadeOnDelete();
            $table->date('tanggal_pengajuan');
            $table->date('tanggal_service')->nullable();
            $table->text('deskripsi');
            $table->boolean('menunggu_mesin_bebas')->default(false);
            $table->foreignId('status_id')->nullable()->constrained('statuses')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jadwal_service_mesins');
    }
};
