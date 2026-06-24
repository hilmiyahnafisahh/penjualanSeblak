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
        Schema::create('market_trend', function (Blueprint $table) {
            $table->id();
            $table->string('nama_tren'); // Contoh: Tren Mukena Silk 2026
            $table->text('analisis_ai'); // Teks penjelasan dari Gemini
            // Menggunakan json untuk menyimpan daftar URL gambar referensi
            $table->json('referensi_visual')->nullable(); 
            $table->string('saran_bahan')->nullable(); // Misal: Armani Silk, Rayon
            $table->string('warna_populer')->nullable(); // Misal: Sage Green, Teracotta
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('market_trend');
    }
};