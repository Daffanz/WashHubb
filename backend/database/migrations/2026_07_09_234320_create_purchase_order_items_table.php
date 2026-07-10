<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('po_id')->constrained('purchase_orders')->cascadeOnDelete();
            $table->morphs('item'); // item_type + item_id (BahanBaku or Mesin)
            $table->decimal('jumlah', 12, 4);
            $table->decimal('harga_satuan', 12, 2);
            $table->decimal('subtotal', 14, 2)->storedAs('jumlah * harga_satuan');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_order_items');
    }
};
