<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('laporan_penjualan_ai', function (Blueprint $table) {
            $table->id();
            $table->string('periode');          // format: YYYY-MM-DD / YYYY-MM / YYYY-Www
            $table->string('tipe_periode')->default('monthly'); // daily|weekly|monthly
            $table->unsignedInteger('total_pesanan')->default(0);
            $table->unsignedInteger('total_qty')->default(0);
            $table->decimal('total_pendapatan', 15, 2)->default(0);
            $table->json('top_menu')->nullable();
            $table->json('top_topping')->nullable();
            $table->json('detail_rows')->nullable();
            // Hasil AI
            $table->string('status_penjualan')->nullable(); // Tinggi|Sedang|Rendah
            $table->text('ringkasan')->nullable();
            $table->json('rekomendasi')->nullable();
            $table->text('proyeksi')->nullable();
            $table->longText('raw_response')->nullable();
            $table->timestamps();

            $table->unique(['periode', 'tipe_periode']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('laporan_penjualan_ai');
    }
};
