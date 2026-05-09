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
        'harga_jual',
        'subtotal',
        'catatan',
    ];

    protected $casts = [
        'harga_jual' => 'integer',
        'subtotal' => 'integer',
        'jumlah' => 'integer',
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