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
        'id_pemesanan',
        'metode_pembayaran',
        'tanggal_pembayaran',
        'total_pembayaran',
        'status_pembayaran',
    ];

    protected $casts = [
        'tanggal_pembayaran' => 'datetime',
        'total_pembayaran'   => 'decimal:2',
    ];

    // RELASI KE PEMESANAN
    public function pemesanan()
    {
        return $this->belongsTo(Pemesanan::class, 'id_pemesanan');
    }

    public static function getKodePembayaran()
    {
        $last = self::latest('id')->first();

        if (!$last) {
            return 'BYR-0001';
        }

        // Ambil angka dari kode terakhir
        $lastNumber = (int) substr($last->id_pembayaran, 4);

        // Tambah 1
        $newNumber = $lastNumber + 1;

        // Format jadi BYR-0001
        return 'BYR-' . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }
}