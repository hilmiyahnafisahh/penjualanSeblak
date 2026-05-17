<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pemesanan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_pelanggan')->constrained('pelanggan')->onDelete('cascade');
            $table->foreignId('id_layanan')->constrained('layanan')->onDelete('cascade');
            $table->string('id_pesanan')->unique();
            $table->dateTime('tanggal_pemesanan');
            $table->enum('status_pemesanan', ['belumdibayar', 'diproses', 'selesai'])->default('belumdibayar');
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pemesanan');
    }
};