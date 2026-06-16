<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Midtrans\Config;
use Midtrans\Transaction;

Config::$serverKey    = env('MIDTRANS_SERVER_KEY');
Config::$isProduction = filter_var(env('MIDTRANS_IS_PRODUCTION', false), FILTER_VALIDATE_BOOLEAN);
Config::$isSanitized  = true;
Config::$is3ds        = true;

// Cek semua pembayaran QRIS yang masih pending dan punya midtrans_order_id
$pembayarans = DB::table('pembayaran')
    ->where('metode_pembayaran', 'qris')
    ->where('status_pembayaran', 'pending')
    ->whereNotNull('midtrans_order_id')
    ->get();

echo "Mengecek " . count($pembayarans) . " pembayaran pending...\n";

foreach ($pembayarans as $p) {
    echo "Cek: {$p->midtrans_order_id} ... ";
    try {
        $status = Transaction::status($p->midtrans_order_id);
        $ts = $status->transaction_status ?? 'unknown';
        $fs = $status->fraud_status ?? 'accept';

        $lunas = in_array($ts, ['capture','settlement']) && ($fs === 'accept' || !$fs);

        if ($lunas) {
            DB::table('pembayaran')->where('id', $p->id)->update(['status_pembayaran' => 'lunas']);
            DB::table('pemesanan')->where('id', $p->id_pemesanan)->update(['status_pemesanan' => 'diproses']);
            echo "✅ LUNAS! Diupdate.\n";
        } else {
            echo "Status: {$ts}\n";
        }
    } catch (\Exception $e) {
        echo "❌ Error: " . $e->getMessage() . "\n";
    }
}
echo "\nSelesai!\n";
