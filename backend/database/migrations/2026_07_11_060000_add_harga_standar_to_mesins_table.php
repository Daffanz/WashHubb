<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mesins', function (Blueprint $table) {
            $table->decimal('harga_standar', 12, 2)->default(0)->after('kapasitas');
        });
    }

    public function down(): void
    {
        Schema::table('mesins', function (Blueprint $table) {
            $table->dropColumn('harga_standar');
        });
    }
};
