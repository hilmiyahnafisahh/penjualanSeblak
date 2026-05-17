<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
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

        // Optional: ukuran & orientasi kertas
        $pdf->setPaper('A4', 'landscape');

        // Download PDF
        return $pdf->download('pembelian.pdf');

        // Kalau mau tampil di browser:
        // return $pdf->stream('pembelian-list.pdf');
    }
}
