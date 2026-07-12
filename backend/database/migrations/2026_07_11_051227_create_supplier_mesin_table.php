<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('supplier_mesin', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')->constrained('suppliers')->cascadeOnDelete();
            $table->foreignId('mesin_id')->constrained('mesins')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['supplier_id', 'mesin_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supplier_mesin');
    }
};
