<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('jadwal_shift_staf_details', function (Blueprint $table) {
            $table->foreignId('status_id')->nullable()->after('jam_selesai')->constrained('statuses')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('jadwal_shift_staf_details', function (Blueprint $table) {
            $table->dropForeign(['status_id']);
            $table->dropColumn('status_id');
        });
    }
};
