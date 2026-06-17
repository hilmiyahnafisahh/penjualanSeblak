<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cashflows', function (Blueprint $table) {
            $table->id();
            $table->string('periode')->unique();
            $table->decimal('total_masuk', 20, 2)->default(0);
            $table->decimal('total_keluar', 20, 2)->default(0);
            $table->decimal('arus_bersih', 20, 2)->default(0);
            $table->decimal('saldo_awal', 20, 2)->default(0);
            $table->decimal('saldo_akhir', 20, 2)->default(0);
            $table->json('laporan')->nullable();
            $table->string('status_kesehatan')->nullable();
            $table->text('ringkasan')->nullable();
            $table->json('rekomendasi')->nullable();
            $table->text('proyeksi')->nullable();
            $table->longText('raw_response')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cashflows');
    }
};
