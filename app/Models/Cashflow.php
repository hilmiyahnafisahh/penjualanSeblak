<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cashflow extends Model
{
    protected $table = 'cashflows';

    protected $fillable = [
        'periode',
        'total_masuk',
        'total_keluar',
        'arus_bersih',
        'saldo_awal',
        'saldo_akhir',
        'laporan',
        'status_kesehatan',
        'ringkasan',
        'rekomendasi',
        'proyeksi',
        'raw_response',
    ];

    protected $casts = [
        'laporan'     => 'array',
        'rekomendasi' => 'array',
    ];
}
