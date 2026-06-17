<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cashflow extends Model
{
    use HasFactory;

    protected $table = 'cashflow'; // Nama tabel eksplisit

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

    // JSON otomatis jadi array saat diakses
    protected $casts = [
        'rekomendasi'  => 'array',
        'laporan'      => 'array',
        'total_masuk'  => 'decimal:2',
        'total_keluar' => 'decimal:2',
        'arus_bersih'  => 'decimal:2',
        'saldo_awal'   => 'decimal:2',
        'saldo_akhir'  => 'decimal:2',
    ];
}