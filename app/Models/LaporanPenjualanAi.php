<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LaporanPenjualanAi extends Model
{
    protected $table = 'laporan_penjualan_ai';

    protected $fillable = [
        'periode',
        'tipe_periode',
        'total_pesanan',
        'total_qty',
        'total_pendapatan',
        'top_menu',
        'top_topping',
        'detail_rows',
        'status_penjualan',
        'ringkasan',
        'rekomendasi',
        'proyeksi',
        'raw_response',
    ];

    protected $casts = [
        'top_menu'         => 'array',
        'top_topping'      => 'array',
        'detail_rows'      => 'array',
        'rekomendasi'      => 'array',
        'total_pendapatan' => 'decimal:2',
    ];
}
