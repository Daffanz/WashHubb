<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('jadwal_shift_staf_details', function (Blueprint $table) {
            // Check if user_id column exists before dropping
            if (Schema::hasColumn('jadwal_shift_staf_details', 'user_id')) {
                // Drop foreign key if it exists
                $foreignKeys = DB::select("SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_NAME = 'jadwal_shift_staf_details' AND COLUMN_NAME = 'user_id' AND CONSTRAINT_NAME != 'PRIMARY'");
                foreach ($foreignKeys as $fk) {
                    $table->dropForeign($fk->CONSTRAINT_NAME);
                }
                $table->dropColumn('user_id');
            }

            // Add nama_karyawan column if it doesn't exist
            if (!Schema::hasColumn('jadwal_shift_staf_details', 'nama_karyawan')) {
                $table->string('nama_karyawan')->after('jadwal_shift_staf_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('jadwal_shift_staf_details', function (Blueprint $table) {
            if (Schema::hasColumn('jadwal_shift_staf_details', 'nama_karyawan')) {
                $table->dropColumn('nama_karyawan');
            }

            if (!Schema::hasColumn('jadwal_shift_staf_details', 'user_id')) {
                $table->foreignId('user_id', 'jssd_user_fk')->constrained('users')->cascadeOnDelete();
            }
        });
    }
};
