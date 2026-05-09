<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class menubarang extends Model
{
    protected $table = 'menubarang';

    protected $guarded = [];
    public function menu()
    {
        return $this->belongsTo(Menu::class, 'id_menu');
    }

    public function barang()
    {
        return $this->belongsTo(Barang::class, 'id_barang');
    }
}
