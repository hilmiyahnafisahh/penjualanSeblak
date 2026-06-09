<?php

namespace App\Mail;

use App\Models\Penggajian;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PenggajianDibayarkan extends Mailable
{
    use Queueable, SerializesModels;

    public Penggajian $penggajian;

    public function __construct(Penggajian $penggajian)
    {
        $this->penggajian = $penggajian; //menginisialisasi properti penggajian dengan data penggajian yang diterima melalui konstruktor, data ini akan digunakan
    }

    public function build()
    {
        return $this->subject('Penggajian Dibayarkan') //mengatur subjek email menjadi 'Penggajian Dibayarkan' untuk memberi tahu penerima bahwa email ini berisi informasi tentang penggajian yang telah berhasil dibayarkan
            ->view('emails.penggajian_dibayarkan'); //mengembalikan view email yang akan digunakan untuk isi email, view ini akan berada di resources/views/emails/penggajian_dibayarkan.blade.php dan akan menampilkan informasi tentang penggajian yang berhasil dibayarkan
    }
}
