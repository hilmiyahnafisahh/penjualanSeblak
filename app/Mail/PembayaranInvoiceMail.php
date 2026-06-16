<?php

namespace App\Mail;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PembayaranInvoiceMail extends Mailable
{
    use Queueable, SerializesModels;

    public array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function build()
    {
        $pdf = Pdf::loadView('pdf.invoice_pembayaran', $this->data)
            ->setPaper('A4', 'portrait');

        return $this->subject('Invoice Pembayaran Seblak')
            ->view('emails.invoice')
            ->with(['data' => $this->data])
            ->attachData($pdf->output(), 'invoice-pembayaran-' . ($this->data['no_pembayaran'] ?? 'invoice') . '.pdf', [
                'mime' => 'application/pdf',
            ]);
    }
}
