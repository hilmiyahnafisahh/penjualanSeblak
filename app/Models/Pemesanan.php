<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Models\DetailPesanan;
use App\Models\Pembayaran;

class Pemesanan extends Model
{
    protected $table      = 'pemesanan';
    protected $primaryKey = 'id';
    protected $fillable   = [
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
            // Hitung subtotal dari menu + topping
            $pemesanan->subtotal = $pemesanan->hitungGrandTotal();
        });
    }

    public static function getKodeFaktur()
    {
        $last = self::latest('id')->first();

        if (!$last) {
            return 'PSN-0001';
        }

        $lastNumber = (int) substr($last->id_pesanan, 4);
        $newNumber  = $lastNumber + 1;

        return 'PSN-' . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Hitung total semua menu + semua topping.
     */
    public function hitungGrandTotal(): int
    {
        $total = 0;

        foreach ($this->DetailPesanan as $detail) {
            $total += (int) ($detail->subtotal ?? 0);

            // Tambahkan subtotal topping dari JSON
            $toppingList = is_array($detail->topping)
                ? $detail->topping
                : json_decode($detail->topping ?? '[]', true);

            if (!empty($toppingList)) {
                foreach ($toppingList as $top) {
                    $qty    = (int) ($top['qty']    ?? 0);
                    $harga  = (float) ($top['harga'] ?? 0);
                    $total += (int) ($top['subtotal'] ?? ($qty * $harga));
                }
            }
        }

        return $total;
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

    public function pembayaran()
    {
        return $this->hasOne(Pembayaran::class, 'id_pemesanan');
    }

    /**
     * Accessor subtotal — ikut hitung topping.
     */
    public function getSubtotalAttribute(): int
    {
        return $this->hitungGrandTotal();
    }
}