<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PengirimanEmail extends Model
{
    protected $table = 'pengiriman_email';

    protected $fillable = [
        'pemesanan_id',
        'status',
        'tgl_pengiriman_pesan',
    ];

    protected $casts = [
        'tgl_pengiriman_pesan' => 'datetime',
    ];

    public function pemesanan()
    {
        return $this->belongsTo(Pemesanan::class, 'pemesanan_id');
    }
}