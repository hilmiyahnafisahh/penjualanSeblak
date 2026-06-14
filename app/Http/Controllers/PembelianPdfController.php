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
            'karyawan'
        ])->get();

        // Load view PDF
        $pdf = Pdf::loadView('pdf.pembelian', compact('pembelian'));

        // Ukuran kertas
        $pdf->setPaper('A4', 'landscape');

        return $pdf->stream('pembelian.pdf');

        // return $pdf->download('pembelian.pdf');
    }
}