<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pembayaran extends Model
{
    use HasFactory;

    protected $table = 'pembayaran';

    protected $fillable = [
        'id_pembayaran',
        'order_id',
        'id_pemesanan',
<<<<<<< HEAD
        'id_karyawan',
=======
        'midtrans_order_id',
>>>>>>> 53a5e1e62c8ff8c772e189f92a427085353cdc4b
        'metode_pembayaran',
        'tanggal_pembayaran',
        'total_pembayaran',
        'status_pembayaran',
    ];

    protected $casts = [
        'tanggal_pembayaran' => 'datetime',
        'total_pembayaran'   => 'decimal:2',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELASI
    |--------------------------------------------------------------------------
    */

    public function pemesanan()
    {
        return $this->belongsTo(Pemesanan::class, 'id_pemesanan');
    }

    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class, 'id_karyawan');
    }

    /*
    |--------------------------------------------------------------------------
    | GENERATE KODE PEMBAYARAN
    |--------------------------------------------------------------------------
    */

    public static function getKodePembayaran()
    {
        // ambil data pembayaran terakhir
        $lastPembayaran = self::orderBy('id', 'desc')->first();

        // kalau belum ada data
        if (!$lastPembayaran) {
            return 'BYR-0001';
        }

        // ambil angka dari kode terakhir
        // contoh: BYR-0001 -> 1
        $lastNumber = (int) str_replace(
            'BYR-',
            '',
            $lastPembayaran->id_pembayaran
        );

        // tambah 1
        $newNumber = $lastNumber + 1;

        // format ulang
        return 'BYR-' . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    /*
    |--------------------------------------------------------------------------
    | AUTO GENERATE SAAT CREATE
    |--------------------------------------------------------------------------
    */

    protected static function booted()
    {
        static::creating(function ($pembayaran) {

            // otomatis isi id_pembayaran jika kosong
            if (empty($pembayaran->id_pembayaran)) {

                $pembayaran->id_pembayaran =
                    self::getKodePembayaran();
            }
        });
    }
}