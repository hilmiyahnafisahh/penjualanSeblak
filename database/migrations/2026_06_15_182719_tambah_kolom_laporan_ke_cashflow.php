<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cashflow', function (Blueprint $table) {
            $table->decimal('saldo_awal', 15, 2)->default(0)->after('arus_bersih');
            $table->decimal('saldo_akhir', 15, 2)->default(0)->after('saldo_awal');
            $table->json('laporan')->nullable()->after('saldo_akhir');
        });
    }

    public function down(): void
    {
        Schema::table('cashflow', function (Blueprint $table) {
            $table->dropColumn(['saldo_awal', 'saldo_akhir', 'laporan']);
        });
    }
};