<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('jurnal_detail')) {
            Schema::create('jurnal_detail', function (Blueprint $table) {
                $table->id();
                $table->foreignId('jurnal_id')->constrained('jurnal')->cascadeOnDelete();
                $table->foreignId('kode_akun')->constrained('akun')->cascadeOnDelete();
                $table->string('deskripsi')->nullable();
                $table->decimal('debit', 15, 2)->default(0);
                $table->decimal('credit', 15, 2)->default(0);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('jurnal_detail');
    }
};