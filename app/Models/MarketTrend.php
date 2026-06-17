<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MarketTrend extends Model
{
    use HasFactory;

    // tambahan penyebutan tabel secara eksplisit
    protected $table = 'market_trend'; // Nama tabel eksplisit

    // Kolom yang boleh diisi manual atau lewat create()
    protected $fillable = [
        'nama_tren',
        'analisis_ai',
        'referensi_visual',
        'saran_bahan',
        'warna_populer',
    ];

    // Casting agar Laravel otomatis mengubah JSON menjadi Array saat diakses
    protected $casts = [
        'referensi_visual' => 'array',
    ];
}