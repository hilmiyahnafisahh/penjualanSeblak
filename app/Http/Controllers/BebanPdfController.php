<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CatatBeban;
use Barryvdh\DomPDF\Facade\Pdf;

class BebanPdfController extends Controller
{
    public function download($id)
    {
        $beban = CatatBeban::findOrFail($id);

        // format data seperti contoh
        $data = [
            'invoice_number' => 'INV-BEBAN-' . $beban->id_beban,
            'tanggal' => \Carbon\Carbon::parse($beban->tanggal)->format('d M Y'),
            'jenis_beban' => $beban->jenis_beban,
            'keterangan' => $beban->keterangan,
            'status' => strtoupper($beban->status),
            'total' => $beban->total,
        ];

        // load view pdf
        $pdf = Pdf::loadView('pdf.invoice', $data)
                  ->setPaper('A4', 'portrait');

        return $pdf->download('invoice-beban-' . $beban->id_beban . '.pdf');
    }
} 