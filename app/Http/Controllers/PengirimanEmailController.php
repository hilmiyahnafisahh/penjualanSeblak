<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PengirimanEmail;
use App\Models\Pemesanan;
use App\Mail\InvoiceMail;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class PengirimanEmailController extends Controller
{
    /**
     * Tampilkan daftar riwayat pengiriman email.
     */
    public function index()
    {
        $pengirimanEmails = PengirimanEmail::with('pemesanan.Pelanggan')
            ->latest()
            ->paginate(10);

        return view('pengiriman_email.index', compact('pengirimanEmails'));
    }

    /**
     * Dipanggil oleh route /proses_pengiriman_email_pembayaran (autorefresh).
     * Kirim invoice ke semua pemesanan yang belum dikirim emailnya.
     */
    public function kirimSemua()
    {
        date_default_timezone_set('Asia/Jakarta');

        // Ambil semua pemesanan yang belum pernah dikirim emailnya
        $pemesananList = Pemesanan::with(['Pelanggan', 'DetailPesanan.menu', 'pembayaran'])
            ->whereNotIn('id', function ($query) {
                $query->select('pemesanan_id')->from('pengiriman_email');
            })
            ->get();

        foreach ($pemesananList as $pemesanan) {
            $emailPelanggan = $pemesanan->Pelanggan->email ?? null;

            if (!$emailPelanggan) {
                continue;
            }

            try {
                $this->prosesKirimEmail($pemesanan);
                sleep(5); // delay 5 detik antar email (batas mailtrap free)

            } catch (\Exception $e) {
                Log::error('Gagal kirim email [' . $pemesanan->id_pesanan . ']: ' . $e->getMessage());

                PengirimanEmail::updateOrCreate(
                    ['pemesanan_id' => $pemesanan->id],
                    [
                        'status'               => 'gagal',
                        'tgl_pengiriman_pesan' => now(),
                    ]
                );
            }
        }

        return view('autorefresh_email');
    }

    /**
     * Kirim invoice ke satu pemesanan (dipanggil dari tombol).
     */
    public function kirim($id)
    {
        $pemesanan = Pemesanan::with([
            'Pelanggan',
            'DetailPesanan.menu',
            'pembayaran',
        ])->findOrFail($id);

        $emailPelanggan = $pemesanan->Pelanggan->email ?? null;

        if (!$emailPelanggan) {
            return back()->with('error', 'Pelanggan tidak memiliki alamat email.');
        }

        try {
            $this->prosesKirimEmail($pemesanan);
            return back()->with('success', 'Invoice berhasil dikirim ke ' . $emailPelanggan);

        } catch (\Exception $e) {
            Log::error('Gagal kirim email invoice [' . $pemesanan->id_pesanan . ']: ' . $e->getMessage());

            PengirimanEmail::updateOrCreate(
                ['pemesanan_id' => $pemesanan->id],
                [
                    'status'               => 'gagal',
                    'tgl_pengiriman_pesan' => now(),
                ]
            );

            return back()->with('error', 'Gagal mengirim email: ' . $e->getMessage());
        }
    }

    /**
     * Proses generate PDF dan kirim email (dipakai oleh kirim() dan kirimSemua()).
     */
    private function prosesKirimEmail($pemesanan)
    {
        $pembayaran = $pemesanan->pembayaran;

        // Hitung grand total termasuk topping
        $grandTotal = 0;
        foreach ($pemesanan->DetailPesanan as $detail) {
            $grandTotal += $detail->subtotal ?? 0;

            if (!empty($detail->topping) && is_array($detail->topping)) {
                foreach ($detail->topping as $top) {
                    $qty   = $top['qty']   ?? 0;
                    $harga = $top['harga'] ?? 0;
                    $grandTotal += $top['subtotal'] ?? ($qty * $harga);
                }
            }
        }

        // Data untuk PDF
        $items             = $pemesanan->DetailPesanan;
        $no_faktur         = $pemesanan->id_pesanan;
        $no_pemesanan      = $pemesanan->id_pesanan;
        $no_pembayaran     = $pembayaran?->id_pembayaran ?? '-';
        $nama_pembeli      = $pemesanan->Pelanggan->nama_pelanggan ?? 'Pelanggan';
        $tanggal           = $pembayaran?->tanggal_pembayaran
                                ? Carbon::parse($pembayaran->tanggal_pembayaran)->format('d-m-Y')
                                : Carbon::parse($pemesanan->tanggal_pemesanan)->format('d-m-Y');
        $metode_pembayaran = $pembayaran?->metode_pembayaran ?? '-';
        $status_pembayaran = $pembayaran?->status_pembayaran ?? $pemesanan->status_pemesanan ?? '-';
        $total             = $pembayaran?->total_pembayaran ?? $grandTotal;

        // Generate PDF
        $pdf = Pdf::loadView('pdf.invoice', compact(
            'items', 'no_faktur', 'no_pemesanan', 'no_pembayaran',
            'nama_pembeli', 'tanggal', 'metode_pembayaran', 'status_pembayaran', 'total'
        ));

        // Kirim email
        Mail::to($pemesanan->Pelanggan->email, $nama_pembeli)
            ->send(new InvoiceMail($pemesanan, $pdf->output()));

        // Simpan riwayat
        PengirimanEmail::updateOrCreate(
            ['pemesanan_id' => $pemesanan->id],
            [
                'status'               => 'terkirim',
                'tgl_pengiriman_pesan' => now(),
            ]
        );
    }

    /**
     * Hapus riwayat pengiriman email.
     */
    public function destroy($id)
    {
        $pengirimanEmail = PengirimanEmail::findOrFail($id);
        $pengirimanEmail->delete();

        return back()->with('success', 'Riwayat pengiriman email berhasil dihapus.');
    }
}