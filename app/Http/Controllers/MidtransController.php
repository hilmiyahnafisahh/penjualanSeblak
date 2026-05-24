<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CatatBeban;
use App\Models\Pembayaran;
use App\Models\Pemesanan;

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

        // Simpan order_id ke database
        $pembayaran->update(['order_id' => $order_id]);

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


    // ======================================================
    // CECK STATUS PEMBAYARAN
    // ======================================================

    public function checkStatus($id)
    {
        $pembayaran = Pembayaran::find($id);

        if (!$pembayaran) {
            return response()->json(['error' => 'Pembayaran tidak ditemukan'], 404);
        }

        // Jika sudah lunas, langsung return
        if ($pembayaran->status_pembayaran === 'lunas') {
            return response()->json([
                'id' => $pembayaran->id,
                'status' => $pembayaran->status_pembayaran,
                'metode' => $pembayaran->metode_pembayaran,
                'pesanan_status' => $pembayaran->pemesanan?->status_pemesanan,
            ]);
        }

        // Jika metode bukan midtrans, langsung return status yang ada
        if (!in_array($pembayaran->metode_pembayaran, ['qris', 'transfer'])) {
            return response()->json([
                'id' => $pembayaran->id,
                'status' => $pembayaran->status_pembayaran,
                'metode' => $pembayaran->metode_pembayaran,
                'pesanan_status' => $pembayaran->pemesanan?->status_pemesanan,
            ]);
        }

        // Verifikasi dengan Midtrans API untuk transaksi QRIS/Transfer
        $this->verifyMidtransPayment($pembayaran);

        $pembayaran->refresh();

        return response()->json([
            'id' => $pembayaran->id,
            'status' => $pembayaran->status_pembayaran,
            'metode' => $pembayaran->metode_pembayaran,
            'pesanan_status' => $pembayaran->pemesanan?->status_pemesanan,
        ]);
    }

    // ======================================================
    // VERIFIKASI STATUS DARI MIDTRANS API
    // ======================================================

    private function verifyMidtransPayment($pembayaran)
    {
        try {
            $this->configureMidtrans();

            // Jika order_id tersimpan, query status dari Midtrans
            if ($pembayaran->order_id) {
                $status = \Midtrans\Transaction::status($pembayaran->order_id);

                if ($status) {
                    $transaction_status = $status->transaction_status ?? null;
                    $fraud_status = $status->fraud_status ?? null;

                    // Update status berdasarkan response Midtrans
                    if ($transaction_status == 'capture' || $transaction_status == 'settlement') {
                        if ($fraud_status != 'deny') {
                            $pembayaran->status_pembayaran = 'lunas';
                            $pembayaran->save();

                            // Update status pesanan jika pembayaran lunas
                            if ($pembayaran->pemesanan) {
                                $pembayaran->pemesanan->update(['status_pemesanan' => 'selesai']);
                            }
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            \Log::error('Midtrans verification error: ' . $e->getMessage());
        }
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

        return response()->json([
            'message' => 'Order ID format tidak dikenali'
        ], 400);
    }
}
