<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// untuk tambahan db
use Illuminate\Support\Facades\DB;
use App\Models\DetailPesanan;

class Pemesanan extends Model
{
    protected $table = 'pemesanan';
    protected $primaryKey = 'id';
    protected $fillable = [
        'id_pelanggan',
        'id_layanan',
        'id_pesanan',
        'tanggal_pemesanan',
        'status_pemesanan',
        'subtotal',
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($pemesanan) {
            $pemesanan->subtotal = $pemesanan->DetailPesanan->sum('subtotal');
        });
    }

    public static function getKodeFaktur()
{
    $last = self::latest('id')->first();

    if (!$last) {
        return 'PSN-0001';
    }

    // Ambil angka dari kode terakhir
    $lastNumber = (int) substr($last->id_pesanan, 4);

    // Tambah 1
    $newNumber = $lastNumber + 1;

    // Format jadi PSN-0001
    return 'PSN-' . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
}

    public function Pelanggan()
    {
        return $this->belongsTo(Pelanggan::class, 'id_pelanggan');
    }
    public function Layanan()
    {
        return $this->belongsTo(Layanan::class, 'id_layanan');
    }
    public function DetailPesanan()
    {
        return $this->hasMany(DetailPesanan::class, 'id_pemesanan');
    }
    public function menu()
    {
        return $this->hasMany(Pemesanan::class, 'id_pemesanan');
    }

    public function getSubtotalAttribute()
    {
        return $this->DetailPesanan->sum('subtotal');
    }
}
