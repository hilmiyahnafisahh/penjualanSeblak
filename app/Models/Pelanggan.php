<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pelanggan extends Model
{
    use HasFactory;

    protected $table = 'pelanggan';

    protected $guarded = [];

    // AUTO GENERATE ID PELANGGAN
    public static function getIDPelanggan()
    {
        $last = self::orderBy('id', 'desc')->first();

        // kalau belum ada data
        if (!$last) {
            return 'PLG001';
        }

        // ambil angka terakhir
        $noawal = substr($last->id_pelanggan, -3);
        $noakhir = (int) $noawal + 1;

        return 'PLG' . str_pad($noakhir, 3, '0', STR_PAD_LEFT);
    }
}