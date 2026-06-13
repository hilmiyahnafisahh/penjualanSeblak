<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('detail_pemesanan', function (Blueprint $table) {
            // Tambah kolom topping (JSON) jika belum ada
            if (!Schema::hasColumn('detail_pemesanan', 'topping')) {
                $table->json('topping')->nullable()->after('catatan');
            }

            // Tambah kolom harga_jual jika belum ada
            if (!Schema::hasColumn('detail_pemesanan', 'harga_jual')) {
                $table->integer('harga_jual')->default(0)->after('harga_menu');
            }
        });
    }

    public function down(): void
    {
        Schema::table('detail_pemesanan', function (Blueprint $table) {
            $table->dropColumn(['topping', 'harga_jual']);
        });
    }
};