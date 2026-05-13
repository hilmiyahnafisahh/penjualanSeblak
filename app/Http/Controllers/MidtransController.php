<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CatatBeban;

use Midtrans\Config;
use Midtrans\Snap;

class MidtransController extends Controller
{
    // ======================================================
    // HALAMAN PEMBAYARAN
    // ======================================================

    public function bayar($id)
    {
        $beban = CatatBeban::findOrFail($id);

        // konfigurasi midtrans
        Config::$serverKey = env('MIDTRANS_SERVER_KEY');
        Config::$isProduction = false;
        Config::$isSanitized = true;
        Config::$is3ds = true;

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


    // ======================================================
    // WEBHOOK / CALLBACK MIDTRANS
    // ======================================================

    public function callback(Request $request)
    {
        Config::$serverKey = env('MIDTRANS_SERVER_KEY');

        $payload = $request->all();

        $order_id = $payload['order_id'] ?? null;
        $transaction_status = $payload['transaction_status'] ?? null;
        $fraud_status = $payload['fraud_status'] ?? null;

        // format:
        // BEBAN-1-123456
        $explode = explode('-', $order_id);

        $id_beban = $explode[1] ?? null;

        $beban = CatatBeban::find($id_beban);

        if (!$beban) {

            return response()->json([
                'message' => 'Data tidak ditemukan'
            ], 404);
        }

        // ======================================================
        // HANDLE STATUS PEMBAYARAN
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
}