<?php

namespace App\Http\Controllers;

use App\Models\Pembelian;
use Barryvdh\DomPDF\Facade\Pdf;

class PembelianPdfController extends Controller
{
    public function pembelian()
    {
        // Ambil data pembelian beserta relasinya
        $pembelian = Pembelian::with([
            'karyawan',
            'pembayaran'
        ])->get();

        // Load view PDF
        $pdf = Pdf::loadView('pdf.pembelian', compact('pembelian'));

        // Ukuran kertas
        $pdf->setPaper('A4', 'landscape');

        // Tampilkan di browser
        return $pdf->stream('pembelian.pdf');

        // Kalau mau langsung download:
        // return $pdf->download('pembelian.pdf');
    }
}