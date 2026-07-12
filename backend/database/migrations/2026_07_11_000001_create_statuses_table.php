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
            $table->string('konteks');
            $table->string('kode');
            $table->string('label');
            $table->unique(['konteks', 'kode']);
        });
    }
    public function down(): void { Schema::dropIfExists('statuses'); }
};
