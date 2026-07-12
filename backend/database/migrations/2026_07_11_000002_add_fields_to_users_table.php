<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->renameColumn('name', 'nama');
            $table->string('no_telp')->nullable()->after('password');
            $table->foreignId('status_id')->nullable()->after('no_telp')->constrained('statuses')->nullOnDelete();
            $table->boolean('wajib_ganti_password')->default(false)->after('status_id');
        });
    }
    public function down(): void {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['no_telp', 'wajib_ganti_password']);
            $table->dropForeignIdFor(\App\Models\Status::class);
            $table->renameColumn('nama', 'name');
        });
    }
};
