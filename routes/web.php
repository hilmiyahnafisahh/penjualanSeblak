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

use App\Http\Controllers\PembelianPdfController;
use App\Http\Controllers\PengirimanEmailController;


// Halaman awal
Route::get('/', function () {
    return view('welcome');
});

// Untuk membuka halaman pembayaran beban
Route::get('/bayar-beban/{id}', [MidtransController::class, 'bayar'])
    ->name('beban.bayar');

// Untuk membuka halaman pembayaran pemesanan lewat Midtrans
Route::get('/bayar-pemesanan/{id}', [MidtransController::class, 'bayarPemesanan'])
    ->name('pembayaran.midtrans');

// Untuk membuka halaman pembayaran penggajian lewat Midtrans
Route::get('/bayar-penggajian/{id}', [MidtransController::class, 'bayarPenggajian'])
    ->name('penggajian.midtrans');

// Untuk menerima hasil sukses pembayaran gaji dari front-end
Route::post('/midtrans/penggajian/success/{id}', [MidtransController::class, 'successPenggajian'])
    ->name('penggajian.midtrans.success');

// Contoh sampel sederhana untuk mengetes midtrans
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
        'nama_pembeli' => $pelanggan?->nama_pelanggan ?? '-',
        'tanggal_pembayaran' => optional($pembayaran->tanggal_pembayaran)->format('d M Y H:i') ?? '-',
        'tanggal' => optional($pembayaran->tanggal_pembayaran)->format('d M Y H:i') ?? '-',
        'metode_pembayaran' => ucfirst($pembayaran->metode_pembayaran ?? '-'),
        'status_pembayaran' => ucfirst($pembayaran->status_pembayaran ?? '-'),
        'total_pembayaran' => $pembayaran->total_pembayaran,
        'total' => $pembayaran->total_pembayaran,
        'items' => $pembayaran->pemesanan?->DetailPesanan ?? collect(),
    ];

    Mail::to($email)->send(new PembayaranInvoiceMail($data));

    return "Invoice dikirim ke {$email} untuk pembayaran id {$id}.";
});

// Untuk mendownload PDF pembelian
Route::get('/pembelian/pdf', [PembelianPdfController::class, 'pembelian'])
    ->name('pembelian.pdf');

// ============================================================
// Pengiriman Email
// ============================================================

// ✅ Route autorefresh — dipanggil dari browser sesuai modul
Route::get('/proses_pengiriman_email_pembayaran', [PengirimanEmailController::class, 'kirimSemua'])
    ->name('pengiriman-email.proses');

// Daftar riwayat pengiriman email
Route::get('/pengiriman-email', [PengirimanEmailController::class, 'index'])
    ->name('pengiriman-email.index');

// Kirim invoice per pesanan dari tombol
Route::get('/pengiriman-email/kirim/{id}', [PengirimanEmailController::class, 'kirim'])
    ->name('pengiriman-email.kirim');

// Hapus riwayat
Route::delete('/pengiriman-email/{id}', [PengirimanEmailController::class, 'destroy'])
    ->name('pengiriman-email.destroy');

