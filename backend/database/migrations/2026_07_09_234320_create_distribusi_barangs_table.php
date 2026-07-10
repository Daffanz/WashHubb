<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('distribusi_barangs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('po_id')->constrained('purchase_orders')->cascadeOnDelete();
            $table->string('nomor_distribusi')->unique();
            $table->foreignId('status_id')->nullable()->constrained('statuses')->nullOnDelete();
            $table->text('catatan')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('distribusi_barangs');
    }
};
