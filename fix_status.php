<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Update pembayaran PLG-19-1781607184 yang sudah settlement
$updated = DB::table('pembayaran')
    ->where('midtrans_order_id', 'PLG-19-1781607184')
    ->update(['status_pembayaran' => 'lunas']);

// Update pemesanan terkait
$bayar = DB::table('pembayaran')->where('midtrans_order_id', 'PLG-19-1781607184')->first();
if ($bayar) {
    DB::table('pemesanan')->where('id', $bayar->id_pemesanan)->update(['status_pemesanan' => 'diproses']);
    echo "Updated pembayaran id={$bayar->id}, pemesanan id={$bayar->id_pemesanan}\n";
}
echo "Done!\n";
