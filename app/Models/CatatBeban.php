<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CatatBeban extends Model
{
    protected $table = 'catat_beban';   // nama tabel 
    protected $primaryKey = 'id_beban'; // Menentukan primary key jika bukan 'id'

    protected $fillable = [
        'kode_akun',
        'tanggal',
        'total',
        'keterangan',
        'jenis_beban',
        'status',
        'gambar'
    ];

    public function akun()
    {
        return $this->belongsTo(Akun::class, 'kode_akun', 'kode_akun'); // relasi dengan akun pada kode_akun
    }
}