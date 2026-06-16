<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$barangs = DB::table('barang')->get();
foreach ($barangs as $b) {
    $hargaJual = (int) round($b->harga_beli * 1.4, 0);
    DB::table('barang')->where('id', $b->id)->update(['harga_jual' => $hargaJual]);
    echo "Updated: {$b->nama_barang} | Beli: {$b->harga_beli} => Jual: {$hargaJual}\n";
}
echo "Selesai!\n";
