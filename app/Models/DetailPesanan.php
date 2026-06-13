<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetailPesanan extends Model
{
    protected $table = 'detail_pemesanan';

    protected $fillable = [
        'id_pemesanan',
        'id_menu',
        'jumlah',
        'harga_menu',
        'harga_jual',   // ← tambahkan
        'subtotal',
        'catatan',
        'topping',      // ← tambahkan agar JSON topping tersimpan
    ];

    protected $casts = [
        'harga_jual' => 'integer',
        'harga_menu' => 'integer',
        'subtotal'   => 'integer',
        'jumlah'     => 'integer',
        'topping'    => 'array',  // ← otomatis encode/decode JSON
    ];

    public function pemesanan()
    {
        return $this->belongsTo(Pemesanan::class, 'id_pemesanan');
    }

    public function menu()
    {
        return $this->belongsTo(Menu::class, 'id_menu');
    }
}