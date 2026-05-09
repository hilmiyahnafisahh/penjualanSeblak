<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pemesanan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_pelanggan')->constrained('pelanggan')->onDelete('cascade');
            $table->foreignId('id_layanan')->constrained('layanan')->onDelete('cascade');
            $table->string('id_pesanan')->unique();
            $table->date('tanggal_pemesanan');
            $table->enum('status_pemesanan', ['pending', 'diproses', 'selesai'])->default('pending');
            $table->string('subtotal', 20);
            $table->timestamps();
        });
    }
};
