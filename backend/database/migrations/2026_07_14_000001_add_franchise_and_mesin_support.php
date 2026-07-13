<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Add manager_outlet_id to outlets table
        Schema::table('outlets', function (Blueprint $table) {
            $table->foreignId('manager_outlet_id')->nullable()->after('franchise_id')->constrained('users')->nullOnDelete();
        });

        // 2. Create stok_outlet_mesins table
        Schema::create('stok_outlet_mesins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('outlet_id', 'som_outlet_fk')->constrained('outlets')->cascadeOnDelete();
            $table->foreignId('mesin_id', 'som_mesin_fk')->constrained('mesins')->cascadeOnDelete();
            $table->integer('stok_saat_ini')->default(0);
            $table->integer('stok_minimum')->default(0);
            $table->integer('stok_masuk')->default(0);
            $table->integer('stok_keluar')->default(0);
            $table->timestamps();
            $table->unique(['outlet_id', 'mesin_id']);
        });

        // 3. Create mutasi_stok_outlet_mesins table
        Schema::create('mutasi_stok_outlet_mesins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stok_outlet_mesin_id', 'msom_stok_fk')->constrained('stok_outlet_mesins')->cascadeOnDelete();
            $table->string('jenis_mutasi');
            $table->integer('jumlah');
            $table->timestamp('tanggal');
            $table->unsignedBigInteger('penerimaan_outlet_detail_id')->nullable();
            $table->timestamps();
        });

        // 4. Add mesin support to permintaan_stok_outlet_details
        Schema::table('permintaan_stok_outlet_details', function (Blueprint $table) {
            $table->foreignId('mesin_id')->nullable()->after('bahan_baku_id')->constrained('mesins')->cascadeOnDelete();
            $table->string('tipe_item')->default('bahan_baku')->after('mesin_id'); // bahan_baku or mesin
        });

        // 5. Add mesin support to distribusi_outlet_details
        Schema::table('distribusi_outlet_details', function (Blueprint $table) {
            $table->foreignId('mesin_id')->nullable()->after('bahan_baku_id')->constrained('mesins')->cascadeOnDelete();
            $table->string('tipe_item')->default('bahan_baku')->after('mesin_id'); // bahan_baku or mesin
        });

        // 6. Add mesin support to penerimaan_outlet_details
        Schema::table('penerimaan_outlet_details', function (Blueprint $table) {
            $table->foreignId('mesin_id')->nullable()->after('distribusi_outlet_detail_id')->constrained('mesins')->cascadeOnDelete();
            $table->string('tipe_item')->default('bahan_baku')->after('mesin_id'); // bahan_baku or mesin
        });
    }

    public function down(): void
    {
        Schema::table('penerimaan_outlet_details', function (Blueprint $table) {
            $table->dropForeign(['mesin_id']);
            $table->dropColumn(['mesin_id', 'tipe_item']);
        });

        Schema::table('distribusi_outlet_details', function (Blueprint $table) {
            $table->dropForeign(['mesin_id']);
            $table->dropColumn(['mesin_id', 'tipe_item']);
        });

        Schema::table('permintaan_stok_outlet_details', function (Blueprint $table) {
            $table->dropForeign(['mesin_id']);
            $table->dropColumn(['mesin_id', 'tipe_item']);
        });

        Schema::dropIfExists('mutasi_stok_outlet_mesins');
        Schema::dropIfExists('stok_outlet_mesins');

        Schema::table('outlets', function (Blueprint $table) {
            $table->dropForeign(['manager_outlet_id']);
            $table->dropColumn('manager_outlet_id');
        });
    }
};
