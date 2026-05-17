<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CatatBeban;
use App\Models\Pembayaran;
use App\Models\Pemesanan;
use App\Models\Penggajian;

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

    public function bayarPenggajian($id)
    {
        $penggajian = Penggajian::findOrFail($id);

        $this->configureMidtrans();

        $order_id = 'PGJ-' . $penggajian->id . '-' . time();

        $params = [
            'transaction_details' => [
                'order_id' => $order_id,
                'gross_amount' => (int) $penggajian->nominal,
            ],
            'item_details' => [
                [
                    'id' => $penggajian->id,
                    'price' => (int) $penggajian->nominal,
                    'quantity' => 1,
                    'name' => 'Gaji ' . ($penggajian->karyawan->nama ?? 'Karyawan'),
                ],
            ],
            'customer_details' => [
                'first_name' => $penggajian->karyawan->nama ?? 'Karyawan',
            ],
        ];

        $snapToken = Snap::getSnapToken($params);

        return view('midtrans.penggajian', compact('snapToken', 'penggajian'));
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
                return response()->json([
                    'message' => 'Data tidak ditemukan'
                ], 404);
            }

            if ($transaction_status == 'capture') {
                if ($fraud_status == 'accept') {
                    $pembayaran->status_pembayaran = 'lunas';
                }
            } elseif ($transaction_status == 'settlement') {
                $pembayaran->status_pembayaran = 'lunas';
            } elseif ($transaction_status == 'pending') {
                $pembayaran->status_pembayaran = 'pending';
            } elseif (
                $transaction_status == 'deny' ||
                $transaction_status == 'expire' ||
                $transaction_status == 'cancel'
            ) {
                $pembayaran->status_pembayaran = 'batal';
            }

            if ($pembayaran->status_pembayaran === 'lunas' && $pembayaran->pemesanan) {
                $pembayaran->pemesanan->update(['status_pemesanan' => 'selesai']);
            }

            $pembayaran->save();

            return response()->json([
                'message' => 'Callback berhasil'
            ]);
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
                $penggajian->status = 'Pending';
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
