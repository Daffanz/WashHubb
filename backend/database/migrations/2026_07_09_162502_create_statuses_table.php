<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('statuses', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('group'); // user, supplier, purchase_order, distribusi
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index(['group', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('statuses');
    }
};
