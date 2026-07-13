<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mutasi_stok_pusat_bahan_bakus', function (Blueprint $table) {
            $table->unsignedBigInteger('retur_detail_id')->nullable()->after('penerimaan_detail_id');
            $table->foreign('retur_detail_id', 'mspb_retur_fk')->references('id')->on('retur_detail_bahan_bakus')->nullOnDelete();
        });

        Schema::table('mutasi_stok_pusat_mesins', function (Blueprint $table) {
            $table->unsignedBigInteger('retur_detail_id')->nullable()->after('penerimaan_detail_id');
            $table->foreign('retur_detail_id', 'mspm_retur_fk')->references('id')->on('retur_detail_mesins')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('mutasi_stok_pusat_bahan_bakus', function (Blueprint $table) {
            $table->dropForeign('mspb_retur_fk');
            $table->dropColumn('retur_detail_id');
        });
        Schema::table('mutasi_stok_pusat_mesins', function (Blueprint $table) {
            $table->dropForeign('mspm_retur_fk');
            $table->dropColumn('retur_detail_id');
        });
    }
};
