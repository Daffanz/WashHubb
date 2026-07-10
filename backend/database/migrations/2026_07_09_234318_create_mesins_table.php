<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mesins', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('kode_mesin')->unique();
            $table->string('merk')->nullable();
            $table->string('tipe')->nullable();
            $table->integer('kapasitas')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mesins');
    }
};
