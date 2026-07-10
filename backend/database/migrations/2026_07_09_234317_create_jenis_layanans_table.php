<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jenis_layanans', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->decimal('harga_standar_per_kg', 12, 2)->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jenis_layanans');
    }
};
