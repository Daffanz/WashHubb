<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('jadwal_service_mesins', function (Blueprint $table) {
            // Check if detail_mesin_id column exists before dropping
            if (Schema::hasColumn('jadwal_service_mesins', 'detail_mesin_id')) {
                // Drop foreign key if it exists
                $foreignKeys = DB::select("SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_NAME = 'jadwal_service_mesins' AND COLUMN_NAME = 'detail_mesin_id' AND CONSTRAINT_NAME != 'PRIMARY'");
                foreach ($foreignKeys as $fk) {
                    $table->dropForeign($fk->CONSTRAINT_NAME);
                }
                $table->dropColumn('detail_mesin_id');
            }

            // Add mesin_id column if it doesn't exist
            if (!Schema::hasColumn('jadwal_service_mesins', 'mesin_id')) {
                $table->foreignId('mesin_id')->after('id')->constrained('mesins')->cascadeOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('jadwal_service_mesins', function (Blueprint $table) {
            if (Schema::hasColumn('jadwal_service_mesins', 'mesin_id')) {
                $table->dropForeign(['mesin_id']);
                $table->dropColumn('mesin_id');
            }

            if (!Schema::hasColumn('jadwal_service_mesins', 'detail_mesin_id')) {
                $table->foreignId('detail_mesin_id', 'jsm_mesin_fk')->constrained('detail_mesins')->cascadeOnDelete();
            }
        });
    }
};
