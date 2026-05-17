<?php

namespace App\Http\Controllers;

use App\Models\Pembayaran;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class PembayaranPdfController extends Controller
{
    public function download($id)
    {
        $pembayaran = Pembayaran::with('pemesanan.DetailPesanan.menu', 'pemesanan.pelanggan')
            ->findOrFail($id);

        $pemesanan = $pembayaran->pemesanan;

        $items = $pemesanan?->DetailPesanan ?? collect();
        $customerName = $pemesanan?->pelanggan?->nama_pelanggan ?? '-';

        $data = [
            'no_pembayaran'   => $pembayaran->id_pembayaran,
            'no_pemesanan'    => $pemesanan?->id_pesanan,
            'nama_pembeli'    => $customerName,
            'tanggal'         => optional($pembayaran->tanggal_pembayaran)->format('d M Y H:i'),
            'metode_pembayaran' => ucfirst($pembayaran->metode_pembayaran),
            'status_pembayaran' => ucfirst($pembayaran->status_pembayaran),
            'items'           => $items,
            'total'           => $pembayaran->total_pembayaran,
        ];

        $pdf = Pdf::loadView('pdf.invoice_pembayaran', $data)
            ->setPaper('A4', 'portrait');

        return $pdf->download('invoice-pembayaran-' . $pembayaran->id_pembayaran . '.pdf');
    }
}
