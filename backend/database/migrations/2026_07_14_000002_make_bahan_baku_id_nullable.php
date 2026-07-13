<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Make bahan_baku_id nullable in permintaan_stok_outlet_details
        Schema::table('permintaan_stok_outlet_details', function (Blueprint $table) {
            $table->foreignId('bahan_baku_id')->nullable()->change();
        });

        // Make bahan_baku_id nullable in distribusi_outlet_details
        Schema::table('distribusi_outlet_details', function (Blueprint $table) {
            $table->foreignId('bahan_baku_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('distribusi_outlet_details', function (Blueprint $table) {
            $table->foreignId('bahan_baku_id')->nullable(false)->change();
        });

        Schema::table('permintaan_stok_outlet_details', function (Blueprint $table) {
            $table->foreignId('bahan_baku_id')->nullable(false)->change();
        });
    }
};
