<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CatatBeban;
use App\Models\Pembayaran;
use App\Models\Pemesanan;
use App\Models\Penggajian;
use App\Mail\PenggajianDibayarkan;
use Illuminate\Support\Facades\Mail;
use Midtrans\Config;
use Midtrans\Snap;

class MidtransController extends Controller
{
    private function configureMidtrans()
    {
        $serverKey = config('services.midtrans.server_key'); 

        if (!$serverKey) {
            abort(500, 'Midtrans server key is not configured. Please set MIDTRANS_SERVER_KEY in your .env file.');
        }

        Config::$serverKey = $serverKey;
        Config::$isProduction = filter_var(config('services.midtrans.is_production', false), FILTER_VALIDATE_BOOLEAN);
        Config::$isSanitized = true;
        Config::$is3ds = true;
    }

    // ======================================================
    // HALAMAN PEMBAYARAN
    // ======================================================

    public function bayar($id)
    {
        $beban = CatatBeban::findOrFail($id);

        $this->configureMidtrans();

        // format order id
        $order_id = 'BEBAN-' . $beban->id_beban . '-' . time(); 

        // data transaksi
        $params = [

            'transaction_details' => [
                'order_id' => $order_id,
                'gross_amount' => (int) $beban->total, 
            ],

            'item_details' => [
                [
                    'id' => $beban->id_beban,
                    'price' => (int) $beban->total,
                    'quantity' => 1,
                    'name' => $beban->jenis_beban,
                ]
            ],

            'customer_details' => [
                'first_name' => 'Admin',
            ],
        ];

        // snap token
        $snapToken = Snap::getSnapToken($params);

        // status sementara
        $beban->update([
            'status' => 'belum lunas',
        ]);

        return view('midtrans.bayar', compact('snapToken', 'beban'));
    }

    public function bayarPemesanan($id)
    {
        $pembayaran = Pembayaran::with('pemesanan.pelanggan')->findOrFail($id);
        $pemesanan = $pembayaran->pemesanan;

        if (!$pemesanan) {
            abort(404, 'Pemesanan tidak ditemukan');
        }

        $this->configureMidtrans();

        // format order id
        $order_id = 'PAY-' . $pembayaran->id . '-' . time();

        // data transaksi
        $params = [
            'transaction_details' => [
                'order_id' => $order_id,
                'gross_amount' => (int) $pembayaran->total_pembayaran,
            ],
            'item_details' => [
                [
                    'id' => $pemesanan->id,
                    'price' => (int) $pembayaran->total_pembayaran,
                    'quantity' => 1,
                    'name' => 'Pesanan ' . $pemesanan->id_pesanan,
                ],
            ],
            'customer_details' => [
                'first_name' => $pemesanan->pelanggan->nama_pelanggan ?? 'Pelanggan',
            ],
        ];

        // snap token
        $snapToken = Snap::getSnapToken($params);

        return view('midtrans.pembayaran', compact('snapToken', 'pembayaran', 'pemesanan'));
    }

    //============PENGGAJIAN================
    public function bayarPenggajian($id) // bayar gaji karyawan
    {
        $penggajian = Penggajian::findOrFail($id); //findOrFail untuk memastikan data penggajian ada, $id adalah id penggajian yang ingin dibayar, $penggajian adalah variabel yang menyimpan data penggajian yang ditemukan berdasarkan id tersebut

        $this->configureMidtrans(); //memanggil method configureMidtrans untuk mengatur konfigurasi Midtrans sebelum melakukan proses pembayaran, seperti mengatur server key, mode produksi, dan lainnya

        $order_id = 'PGJ-' . $penggajian->id . '-' . time(); //membuat order_id unik untuk transaksi pembayaran gaji, formatnya adalah PGJ- diikuti dengan id penggajian dan timestamp saat ini, order_id ini akan digunakan untuk mengidentifikasi transaksi pembayaran gaji di Midtrans
        //order_id adalah nomor nota pembayaran yang unik untuk setiap transaksi pembayaran gaji, formatnya adalah PGJ- diikuti dengan id penggajian dan timestamp saat ini, misalnya PGJ-123-1616161616
        $params = [ //membuat array $params yang berisi detail transaksi pembayaran gaji, termasuk transaction_details, item_details, dan customer_details yang akan dikirim ke Midtrans untuk memproses pembayaran
            'transaction_details' => [
                'order_id' => $order_id,
                'gross_amount' => (int) $penggajian->nominal, //mengambil nominal gaji dari data penggajian dan mengkonversinya menjadi integer untuk digunakan sebagai gross_amount dalam transaksi pembayaran di Midtrans
            ],
            'item_details' => [
                [
                    'id' => $penggajian->id, //menggunakan id penggajian sebagai id item dalam transaksi pembayaran, ini akan membantu mengidentifikasi item yang dibayar dalam transaksi di Midtrans
                    //id adalah data dari item id penggajian yang akan dibayar, misalnya id penggajian 123
                    'price' => (int) $penggajian->nominal,//mengambil nominal gaji dari data penggajian dan mengkonversinya menjadi integer untuk digunakan sebagai price dalam item_details transaksi pembayaran di Midtrans
                    'quantity' => 1, //1 adalah jumlah item yang dibayar, dalam kasus ini adalah 1 gaji karyawan
                    'name' => 'Gaji ' . ($penggajian->karyawan->nama ?? 'Karyawan'), //menggunakan nama karyawan yang terkait dengan data penggajian untuk membuat nama item dalam transaksi pembayaran, jika nama karyawan tidak tersedia maka akan menggunakan 'Karyawan' sebagai nama item, misalnya 'Gaji John Doe'
                ],
            ],
            'customer_details' => [
                'first_name' => $penggajian->karyawan->nama ?? 'Karyawan', //menggunakan nama karyawan yang terkait dengan data penggajian untuk mengisi first_name dalam customer_details transaksi pembayaran, jika nama karyawan tidak tersedia maka akan menggunakan 'Karyawan' sebagai first_name, misalnya 'John Doe'
            ],
        ];

        if ($penggajian->status !== 'Ditangguhkan') {
            $penggajian->update(['status' => 'Ditangguhkan']);
        }

        $snapToken = Snap::getSnapToken($params); //memanggil method getSnapToken dari class Snap untuk mendapatkan snap token yang akan digunakan untuk memproses pembayaran di Midtrans, snap token ini akan dikirim ke view untuk digunakan dalam proses pembayaran di frontend

        return view('midtrans.penggajian', compact('snapToken', 'penggajian')); //mengembalikan view midtrans.penggajian dengan data snapToken dan penggajian yang akan digunakan
    }

    public function successPenggajian(Request $request, $id) //method ini akan dipanggil ketika pembayaran gaji berhasil, method ini menerima request dari Midtrans dan id penggajian yang dibayar, method ini akan memproses payload dari Midtrans untuk memperbarui status penggajian dan mengirim email notifikasi jika gaji berhasil dibayarkan
    {
        $penggajian = Penggajian::findOrFail($id);
        $payload = $request->all(); //mengambil semua data dari request yang dikirim oleh Midtrans setelah pembayaran berhasil, data ini akan berisi informasi tentang transaksi pembayaran yang dilakukan, seperti order_id, transaction_status, fraud_status, dan lainnya
        $transaction_status = data_get($payload, 'transaction_status') ?? data_get($payload, 'result.transaction_status'); //mengambil nilai transaction_status dari payload, jika tidak ditemukan maka akan mencoba mengambil dari result.transaction_status, transaction_status ini akan digunakan untuk menentukan status penggajian setelah pembayaran berhasil
        $fraud_status = data_get($payload, 'fraud_status') ?? data_get($payload, 'result.fraud_status'); //mengambil nilai fraud_status dari payload, jika tidak ditemukan maka akan mencoba mengambil dari result.fraud_status, fraud_status ini akan digunakan untuk menentukan apakah transaksi pembayaran dianggap aman atau tidak, dan akan mempengaruhi status penggajian setelah pembayaran berhasil

        if (in_array($transaction_status, ['capture', 'settlement'], true)) {
            $penggajian->status = 'Dibayarkan'; //jika transaction_status adalah capture atau settlement, maka status penggajian akan diubah menjadi 'Dib
        } elseif ($transaction_status === 'pending') {
            $penggajian->status = 'Ditangguhkan'; 
        } else {
            $penggajian->status = 'Ditangguhkan';  
        }

        $penggajian->save(); //menyimpan perubahan status penggajian ke database setelah memproses transaction_status dan fraud_status dari payload Midtrans

        if ($penggajian->status === 'Dibayarkan') {
            Mail::to(config('mail.from.address'))->send(new PenggajianDibayarkan($penggajian)); //jika status penggajian adalah 'Dibayarkan', maka akan mengirim email notifikasi menggunakan class PenggajianDibayarkan ke alamat email yang ditentukan dalam konfigurasi mail, email ini akan berisi informasi tentang penggajian yang berhasil dibayarkan
        }

        return response()->json([ //mengembalikan response JSON yang berisi pesan bahwa status penggajian telah diperbarui dan juga menyertakan status penggajian yang baru setelah diproses, response ini akan dikirim kembali ke Midtrans sebagai konfirmasi bahwa callback telah berhasil diproses
            'message' => 'Status penggajian diperbarui',
            'status' => $penggajian->status,
        ]);
    }


    // ======================================================
    // WEBHOOK / CALLBACK MIDTRANS
    // ======================================================

    public function callback(Request $request)
    {
        $this->configureMidtrans();

        $payload = $request->all();

        $order_id = $payload['order_id'] ?? null;
        $transaction_status = $payload['transaction_status'] ?? null;
        $fraud_status = $payload['fraud_status'] ?? null;

        // format:
        // BEBAN-1-123456
        // PAY-123-123456
        $explode = explode('-', $order_id);
        $type = $explode[0] ?? null;

        if ($type === 'BEBAN') {
            $id_beban = $explode[1] ?? null;
            $beban = CatatBeban::find($id_beban);

            if (!$beban) {
                return response()->json([
                    'message' => 'Data tidak ditemukan'
                ], 404);
            }

            // ======================================================
            // HANDLE STATUS PEMBAYARAN BEBAN
            // ======================================================

            if ($transaction_status == 'capture') {

                if ($fraud_status == 'accept') {

                    $beban->status = 'lunas';
                }

            } elseif ($transaction_status == 'settlement') {

                $beban->status = 'lunas';

            } elseif (
                $transaction_status == 'pending' ||
                $transaction_status == 'deny' ||
                $transaction_status == 'expire' ||
                $transaction_status == 'cancel'
            ) {

                $beban->status = 'belum lunas';
            }

            $beban->save();

            return response()->json([
                'message' => 'Callback berhasil'
            ]);
        }

        if ($type === 'PAY') {
            $id_pembayaran = $explode[1] ?? null;
            $pembayaran = Pembayaran::find($id_pembayaran);

            if (!$pembayaran) {
                return response()->json(['message' => 'Data tidak ditemukan'], 404);
            }

            if ($transaction_status == 'capture') {
                if ($fraud_status == 'accept') { $pembayaran->status_pembayaran = 'lunas'; }
            } elseif ($transaction_status == 'settlement') {
                $pembayaran->status_pembayaran = 'lunas';
            } elseif ($transaction_status == 'pending') {
                $pembayaran->status_pembayaran = 'pending';
            } elseif (in_array($transaction_status, ['deny','expire','cancel'])) {
                $pembayaran->status_pembayaran = 'batal';
            }

            if ($pembayaran->status_pembayaran === 'lunas' && $pembayaran->pemesanan) {
                $pembayaran->pemesanan->update(['status_pemesanan' => 'selesai']);
            }
            $pembayaran->save();
            return response()->json(['message' => 'Callback berhasil']);
        }

        // PLG = pembayaran QRIS dari pelanggan
        if ($type === 'PLG') {
            $id_pembayaran = $explode[1] ?? null;
            $pembayaran = Pembayaran::with('pemesanan')->find($id_pembayaran);

            if (!$pembayaran) {
                return response()->json(['message' => 'Data tidak ditemukan'], 404);
            }

            if ($transaction_status == 'capture') {
                if ($fraud_status == 'accept') { $pembayaran->status_pembayaran = 'lunas'; }
            } elseif ($transaction_status == 'settlement') {
                $pembayaran->status_pembayaran = 'lunas';
            } elseif ($transaction_status == 'pending') {
                $pembayaran->status_pembayaran = 'pending';
            } elseif (in_array($transaction_status, ['deny','expire','cancel'])) {
                $pembayaran->status_pembayaran = 'batal';
            }

            if ($pembayaran->status_pembayaran === 'lunas' && $pembayaran->pemesanan) {
                $pembayaran->pemesanan->update(['status_pemesanan' => 'diproses']);
            }
            $pembayaran->save();
            return response()->json(['message' => 'Callback QRIS berhasil']);
        }

        if ($type === 'PGJ') {
            $id_penggajian = $explode[1] ?? null;
            $penggajian = Penggajian::find($id_penggajian);

            if (!$penggajian) {
                return response()->json([
                    'message' => 'Data tidak ditemukan'
                ], 404);
            }

            if ($transaction_status == 'capture') {
                if ($fraud_status == 'accept') {
                    $penggajian->status = 'Dibayarkan';
                }
            } elseif ($transaction_status == 'settlement') {
                $penggajian->status = 'Dibayarkan';
            } elseif ($transaction_status == 'pending') {
                $penggajian->status = 'Ditangguhkan';
            } elseif (
                $transaction_status == 'deny' ||
                $transaction_status == 'expire' ||
                $transaction_status == 'cancel'
            ) {
                $penggajian->status = 'Ditangguhkan';
            }

            $penggajian->save();

            return response()->json([
                'message' => 'Callback berhasil'
            ]);
        }

        return response()->json([
            'message' => 'Order ID format tidak dikenali'
        ], 400);
    }
}
