<?php

namespace App\Http\Controllers;

use App\Mail\PembayaranInvoiceMail;
use App\Models\Pembayaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class PembayaranEmailController extends Controller
{
    public function show($id)
    {
        $pembayaran = Pembayaran::with('pemesanan.Pelanggan', 'pemesanan.DetailPesanan.menu')
            ->findOrFail($id);

        return view('pembayaran.send_invoice', [
            'pembayaran' => $pembayaran,
            'pelangganEmail' => $pembayaran->pemesanan?->Pelanggan?->email,
        ]);
    }

    public function send(Request $request, $id)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $pembayaran = Pembayaran::with('pemesanan.Pelanggan', 'pemesanan.DetailPesanan.menu')
            ->findOrFail($id);

        $pelanggan = $pembayaran->pemesanan?->Pelanggan;
        $recipient = $request->input('email');

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

        Mail::to($recipient)->send(new PembayaranInvoiceMail($data));

        return redirect()->back()->with('success', 'Invoice PDF berhasil dikirim ke ' . $recipient);
    }
}
