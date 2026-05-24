<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MidtransController;
use App\Http\Controllers\BebanPdfController;
use App\Http\Controllers\CobaMidtransController;
use App\Http\Controllers\PembayaranPdfController;
use Illuminate\Support\Facades\Mail;
use App\Mail\PembayaranInvoiceMail;
use App\Models\Pembayaran;
use App\Http\Controllers\PembayaranEmailController;

// halaman awal
Route::get('/', function () {
    return view('welcome');
});

// untuk membuka halaman pembayaran beban
Route::get('/bayar-beban/{id}', [MidtransController::class, 'bayar'])
    ->name('beban.bayar');

// untuk membuka halaman pembayaran pemesanan lewat Midtrans
Route::get('/bayar-pemesanan/{id}', [MidtransController::class, 'bayarPemesanan'])
    ->name('pembayaran.midtrans');

// contoh sampel sederhana untuk mengetes midtrans
Route::get('/cekmidtrans', [CobaMidtransController::class, 'cekmidtrans']);

// Route untuk menampilkan halaman tombol bayar & simulasi
Route::get('/cek-midtrans', [CobaMidtransController::class, 'cekmidtranscallback']);

// Halaman kirim email invoice pembayaran
Route::get('/admin/pembayaran/{id}/send-invoice', [PembayaranEmailController::class, 'show'])
    ->name('pembayaran.send_invoice.form');

// Proses kirim email invoice pembayaran
Route::post('/admin/pembayaran/{id}/send-invoice', [PembayaranEmailController::class, 'send'])
    ->name('pembayaran.send_invoice');

// Unduh invoice PDF pembayaran
Route::get('/admin/pembayaran/{id}/invoice', [PembayaranPdfController::class, 'download'])
    ->name('pembayaran.invoice');

// Route cepat untuk uji pengiriman email (tes lokal)
Route::get('/tesemail', function () {
    $data = [
        'no_pembayaran' => 'TEST-123',
        'customer_name' => 'Tester',
        'total' => 10000,
        'items' => collect(),
        'tanggal' => now()->format('d M Y H:i'),
    ];

    Mail::to('you@example.com')->send(new PembayaranInvoiceMail($data));

    return 'Email test dikirim (cek log/mailtrap).';
});

// Kirim invoice ke email pelanggan berdasarkan id pembayaran
Route::get('/tesemail/{id}', function ($id) {
    $pembayaran = Pembayaran::with('pemesanan.Pelanggan', 'pemesanan.DetailPesanan.menu')
        ->find($id);

    if (! $pembayaran) {
        return "Pembayaran dengan id {$id} tidak ditemukan.";
    }

    $pelanggan = $pembayaran->pemesanan?->Pelanggan;
    $email = $pelanggan?->email;

    if (! $email) {
        return "Email pelanggan tidak tersedia untuk pembayaran id {$id}.";
    }

    $data = [
        'no_pembayaran' => $pembayaran->id_pembayaran,
        'id_pembayaran' => $pembayaran->id_pembayaran,
        'id_pemesanan' => $pembayaran->id_pemesanan,
        'customer_name' => $pelanggan?->nama_pelanggan ?? 'Pelanggan',
        'tanggal_pembayaran' => optional($pembayaran->tanggal_pembayaran)->format('d M Y H:i') ?? '-',
        'metode_pembayaran' => ucfirst($pembayaran->metode_pembayaran ?? '-'),
        'status_pembayaran' => ucfirst($pembayaran->status_pembayaran ?? '-'),
        'total_pembayaran' => $pembayaran->total_pembayaran,
        'items' => $pembayaran->pemesanan?->DetailPesanan ?? collect(),
        'nama_pembeli' => $pelanggan?->nama_pelanggan ?? '-',
        'tanggal' => optional($pembayaran->tanggal_pembayaran)->format('d M Y H:i') ?? '-',
        'metode_pembayaran' => ucfirst($pembayaran->metode_pembayaran ?? '-'),
        'status_pembayaran' => ucfirst($pembayaran->status_pembayaran ?? '-'),
        'total' => $pembayaran->total_pembayaran,
    ];

    Mail::to($email)->send(new PembayaranInvoiceMail($data));

    return "Invoice dikirim ke {$email} untuk pembayaran id {$id}.";
});