<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loyaltis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('outlet_id', 'loy_outlet_fk')->constrained('outlets')->cascadeOnDelete();
            $table->string('periode');
            $table->decimal('target_omset', 14, 2);
            $table->decimal('target_operasional', 5, 2);
            $table->decimal('omset_aktual', 14, 2)->default(0);
            $table->decimal('capaian_operasional', 5, 2)->nullable();
            $table->boolean('memenuhi_target')->default(false);
            $table->decimal('jumlah_bonus', 14, 2)->nullable();
            $table->text('keterangan')->nullable();
            $table->foreignId('status_id')->nullable()->constrained('statuses')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('loyalti_pencairans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loyalti_id', 'lp_loyalti_fk')->constrained('loyaltis')->cascadeOnDelete();
            $table->string('bukti_transfer')->nullable();
            $table->foreignId('status_id')->nullable()->constrained('statuses')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loyalti_pencairans');
        Schema::dropIfExists('loyaltis');
    }
};
