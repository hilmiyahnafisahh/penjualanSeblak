<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// tambahan
use Illuminate\Support\Facades\DB;

class Menu extends Model
{
    protected $table = 'menu'; // Nama tabel eksplisit

    protected $guarded = [];

   public static function getIDMakanan()
{
    $sql = "SELECT IFNULL(MAX(id_menu), 'MKN000') as id_menu 
            FROM menu 
            WHERE id_menu LIKE 'MKN%'";
    
    $result = DB::select($sql);
    $kd = $result[0]->id_menu;

    $noawal = substr($kd,-3);
    $noakhir = (int)$noawal + 1;

    return 'MKN'.str_pad($noakhir,3,"0",STR_PAD_LEFT);
}

public static function getIDMinuman()
{
    $sql = "SELECT IFNULL(MAX(id_menu), 'MNM000') as id_menu 
            FROM menu 
            WHERE id_menu LIKE 'MNM%'";
    
    $result = DB::select($sql);
    $kd = $result[0]->id_menu;

    $noawal = substr($kd,-3);
    $noakhir = (int)$noawal + 1;

    return 'MNM'.str_pad($noakhir,3,"0",STR_PAD_LEFT);  
    }

}
