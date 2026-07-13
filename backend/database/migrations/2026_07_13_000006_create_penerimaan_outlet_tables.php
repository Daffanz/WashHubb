<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('penerimaan_outlets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('distribusi_outlet_id', 'po_dist_fk')->constrained('distribusi_outlets')->cascadeOnDelete();
            $table->foreignId('user_id', 'po_user_fk')->constrained('users')->cascadeOnDelete();
            $table->date('tanggal_terima');
            $table->timestamps();
        });

        Schema::create('penerimaan_outlet_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('penerimaan_outlet_id', 'pod_penerimaan_fk')->constrained('penerimaan_outlets')->cascadeOnDelete();
            $table->foreignId('distribusi_outlet_detail_id', 'pod_dist_fk')->constrained('distribusi_outlet_details')->cascadeOnDelete();
            $table->decimal('qty_diterima', 12, 4);
            $table->timestamps();
        });

        // FK untuk mutasi_stok_outlet ke penerimaan_outlet_detail
        Schema::table('mutasi_stok_outlet_bahan_bakus', function (Blueprint $table) {
            $table->foreign('penerimaan_outlet_detail_id', 'mso_penerimaan_fk')->references('id')->on('penerimaan_outlet_details')->nullOnDelete();
        });

        // FK untuk mutasi_stok_pusat ke penerimaan_outlet_detail (PRD: stok pusat berkurang saat penerimaan outlet)
        Schema::table('mutasi_stok_pusat_bahan_bakus', function (Blueprint $table) {
            $table->unsignedBigInteger('penerimaan_outlet_detail_id')->nullable()->after('retur_detail_id');
            $table->foreign('penerimaan_outlet_detail_id', 'mspb_po_detail_fk')->references('id')->on('penerimaan_outlet_details')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('mutasi_stok_pusat_bahan_bakus', function (Blueprint $table) {
            $table->dropForeign('mspb_po_detail_fk');
            $table->dropColumn('penerimaan_outlet_detail_id');
        });
        Schema::table('mutasi_stok_outlet_bahan_bakus', function (Blueprint $table) {
            $table->dropForeign('mso_penerimaan_fk');
        });
        Schema::dropIfExists('penerimaan_outlet_details');
        Schema::dropIfExists('penerimaan_outlets');
    }
};
