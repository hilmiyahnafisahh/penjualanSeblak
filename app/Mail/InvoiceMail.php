<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InvoiceMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Data pemesanan
     */
    public $pemesanan;

    /**
     * Isi PDF invoice dalam bentuk binary
     */
    public $pdfContent;

    /**
     * Create a new message instance.
     */
    public function __construct($pemesanan, $pdfContent)
    {
        $this->pemesanan  = $pemesanan;
        $this->pdfContent = $pdfContent;
    }

    /**
     * Subject email
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Invoice Pemesanan Seblak #' . $this->pemesanan->id_pesanan,
        );
    }

    /**
     * Isi body email — menggunakan template emails/invoice.blade.php yang sudah ada
     * Template tersebut menggunakan variabel $data['...']
     */
    public function content(): Content
    {
        $pembayaran = $this->pemesanan->pembayaran;

        // Hitung grand total termasuk topping
        $grandTotal = 0;
        foreach ($this->pemesanan->DetailPesanan as $detail) {
            $grandTotal += $detail->subtotal ?? 0;
            if (!empty($detail->topping) && is_array($detail->topping)) {
                foreach ($detail->topping as $top) {
                    $qty   = $top['qty']   ?? 0;
                    $harga = $top['harga'] ?? 0;
                    $grandTotal += $top['subtotal'] ?? ($qty * $harga);
                }
            }
        }

        // Susun $data sesuai variabel di emails/invoice.blade.php
        $data = [
            'customer_name'      => $this->pemesanan->Pelanggan->nama_pelanggan ?? 'Pelanggan',
            'id_pembayaran'      => $pembayaran?->id_pembayaran ?? '-',
            'id_pemesanan'       => $this->pemesanan->id_pesanan,
            'tanggal_pembayaran' => $pembayaran?->tanggal_pembayaran
                                        ? \Carbon\Carbon::parse($pembayaran->tanggal_pembayaran)->format('d-m-Y')
                                        : \Carbon\Carbon::parse($this->pemesanan->tanggal_pemesanan)->format('d-m-Y'),
            'metode_pembayaran'  => $pembayaran?->metode_pembayaran ?? '-',
            'status_pembayaran'  => $pembayaran?->status_pembayaran ?? $this->pemesanan->status_pemesanan ?? '-',
            'total_pembayaran'   => $pembayaran?->total_pembayaran ?? $grandTotal,
        ];

        return new Content(
            view: 'emails.invoice', // → resources/views/emails/invoice.blade.php
            with: compact('data'),
        );
    }

    /**
     * Lampiran PDF
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