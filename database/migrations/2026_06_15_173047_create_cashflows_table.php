<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cashflow', function (Blueprint $table) {
            $table->id();
            $table->string('periode');                      // contoh: 2026-06
            $table->decimal('total_masuk', 15, 2)->default(0);
            $table->decimal('total_keluar', 15, 2)->default(0);
            $table->decimal('arus_bersih', 15, 2)->default(0);
            $table->string('status_kesehatan')->nullable(); // Sehat / Waspada / Kritis
            $table->text('ringkasan')->nullable();
            $table->json('rekomendasi')->nullable();        // array saran
            $table->text('proyeksi')->nullable();
            $table->longText('raw_response')->nullable();    // jawaban mentah AI
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cashflow');
    }
};