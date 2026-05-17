<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InvoicePemesanan extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Data pemesanan
     */
    public $pemesanan;

    /**
     * Isi PDF invoice
     */
    public $pdfContent;

    /**
     * Create a new message instance.
     */
    public function __construct($pemesanan, $pdfContent)
    {
        $this->pemesanan = $pemesanan;
        $this->pdfContent = $pdfContent;
    }

    /**
     * Subject email
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Invoice Pemesanan #' . $this->pemesanan->id_pesanan,
        );
    }

    /**
     * Isi email
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.invoice-pemesanan',

            with: [
                'pemesanan' => $this->pemesanan,
            ],
        );
    }

    /**
     * Attachment PDF
     */
    public function attachments(): array
    {
        return [

            Attachment::fromData(
                fn () => $this->pdfContent,
                'invoice-' . $this->pemesanan->id_pesanan . '.pdf'
            )->withMime('application/pdf'),

        ];
    }
}