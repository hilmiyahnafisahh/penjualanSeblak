<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pemesanan;
use App\Models\Pembayaran;

class PembayaranController extends Controller
{
    public function show($idPemesanan)
    {
        $pemesanan = Pemesanan::with('DetailPesanan.menu', 'pelanggan')->findOrFail($idPemesanan);

        return view('pembayaran.show', compact('pemesanan'));
    }

    public function store(Request $request, $idPemesanan)
    {
        $request->validate([
            'metode_pembayaran' => 'required|in:cash,qris,transfer',
        ]);

        $pemesanan = Pemesanan::findOrFail($idPemesanan);

        $pembayaran = Pembayaran::create([
            'id_pembayaran' => Pembayaran::getKodePembayaran(),
            'id_pemesanan' => $idPemesanan,
            'total_pembayaran' => $pemesanan->subtotal,
            'metode_pembayaran' => $request->metode_pembayaran,
            'tanggal_pembayaran' => now(),
            'status_pembayaran' => $request->metode_pembayaran === 'cash' ? 'lunas' : 'pending',
        ]);

        if ($request->metode_pembayaran === 'cash') {
            $pemesanan->update(['status_pemesanan' => 'selesai']);
            return redirect('/admin/pemesanan')->with('success', 'Pembayaran tunai berhasil!');
        } else {
            // Redirect ke Midtrans
            return redirect()->route('pembayaran.midtrans', ['id' => $pembayaran->id]);
        }
    }
}