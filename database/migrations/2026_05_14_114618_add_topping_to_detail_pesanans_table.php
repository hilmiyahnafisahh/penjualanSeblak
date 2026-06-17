<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('detail_pemesanan', function (Blueprint $table) {
            $table->json('topping')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('detail_pemesanan', function (Blueprint $table) {
            $table->dropColumn('topping');
        });
    }
};