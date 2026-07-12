<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('franchises', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::create('outlets', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('kode_outlet')->unique();
            $table->text('alamat');
            $table->foreignId('franchise_id')->nullable()->constrained('franchises')->nullOnDelete();
            $table->foreignId('status_id')->nullable()->constrained('statuses')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('user_outlets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('outlet_id')->constrained('outlets')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['user_id', 'outlet_id']);
        });
    }
    public function down(): void {
        Schema::dropIfExists('user_outlets');
        Schema::dropIfExists('outlets');
        Schema::dropIfExists('franchises');
    }
};
