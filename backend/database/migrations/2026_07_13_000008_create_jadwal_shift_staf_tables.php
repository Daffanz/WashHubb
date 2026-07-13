<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jadwal_shift_stafs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('outlet_id', 'jss_outlet_fk')->constrained('outlets')->cascadeOnDelete();
            $table->foreignId('user_id', 'jss_user_fk')->constrained('users')->cascadeOnDelete();
            $table->date('minggu_mulai');
            $table->foreignId('status_id')->nullable()->constrained('statuses')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('jadwal_shift_staf_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jadwal_shift_staf_id', 'jssd_shift_fk')->constrained('jadwal_shift_stafs')->cascadeOnDelete();
            $table->foreignId('user_id', 'jssd_user_fk')->constrained('users')->cascadeOnDelete();
            $table->enum('hari', ['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu', 'minggu']);
            $table->time('jam_mulai');
            $table->time('jam_selesai');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jadwal_shift_staf_details');
        Schema::dropIfExists('jadwal_shift_stafs');
    }
};
