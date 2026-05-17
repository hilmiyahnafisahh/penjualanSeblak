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
        $this->penggajian = $penggajian;
    }

    public function build()
    {
        return $this->subject('Penggajian Dibayarkan')
            ->view('emails.penggajian_dibayarkan');
    }
}
