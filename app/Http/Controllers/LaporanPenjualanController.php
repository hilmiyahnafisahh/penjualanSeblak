<?php

namespace App\Http\Controllers;

use App\Models\Pembayaran;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use App\Models\User;

class LaporanPenjualanController extends Controller
{
    /**
     * Guard kasir via session (sama dengan KasirController).
     */
    protected function guardKasir(Request $request): ?User
    {
        $userId = $request->session()->get('kasir_user_id');
        if (!$userId) return null;

        $user = User::find($userId);
        return ($user && $user->user_group === 'kasir') ? $user : null;
    }

    /**
     * Bangun query berdasarkan filter dari request.
     */
    protected function buildQuery(Request $request)
    {
        $dari    = $request->query('dari');
        $sampai  = $request->query('sampai');
        $status  = $request->query('status', 'semua');

        $query = Pembayaran::with('pemesanan.Pelanggan')
            ->orderBy('id', 'desc');

        if ($dari) {
            $query->whereDate('tanggal_pembayaran', '>=', $dari)
                  ->orWhereHas('pemesanan', fn($q) =>
                      $q->whereDate('tanggal_pemesanan', '>=', $dari)
                  );
            // Re-apply to avoid OR bleeding — use a clean scoped approach
            $query = Pembayaran::with('pemesanan.Pelanggan')
                ->orderBy('id', 'desc');

            if ($dari && $sampai) {
                $query->where(function ($q) use ($dari, $sampai) {
                    $q->whereBetween('tanggal_pembayaran', [$dari . ' 00:00:00', $sampai . ' 23:59:59'])
                      ->orWhereHas('pemesanan', fn($q2) =>
                          $q2->whereBetween('tanggal_pemesanan', [$dari . ' 00:00:00', $sampai . ' 23:59:59'])
                      );
                });
            } elseif ($dari) {
                $query->where(function ($q) use ($dari) {
                    $q->where('tanggal_pembayaran', '>=', $dari . ' 00:00:00')
                      ->orWhereHas('pemesanan', fn($q2) =>
                          $q2->where('tanggal_pemesanan', '>=', $dari . ' 00:00:00')
                      );
                });
            } elseif ($sampai) {
                $query->where(function ($q) use ($sampai) {
                    $q->where('tanggal_pembayaran', '<=', $sampai . ' 23:59:59')
                      ->orWhereHas('pemesanan', fn($q2) =>
                          $q2->where('tanggal_pemesanan', '<=', $sampai . ' 23:59:59')
                      );
                });
            }
        } else {
            // rebuild clean
            $query = Pembayaran::with('pemesanan.Pelanggan')
                ->orderBy('id', 'desc');

            if ($sampai) {
                $query->where(function ($q) use ($sampai) {
                    $q->where('tanggal_pembayaran', '<=', $sampai . ' 23:59:59')
                      ->orWhereHas('pemesanan', fn($q2) =>
                          $q2->where('tanggal_pemesanan', '<=', $sampai . ' 23:59:59')
                      );
                });
            }
        }

        if ($status && $status !== 'semua') {
            $query->where('status_pembayaran', $status);
        }

        return $query;
    }

    /**
     * Halaman laporan penjualan (web).
     */
    public function index(Request $request)
    {
        if (!$this->guardKasir($request)) {
            return redirect()->route('kasir.login');
        }

        $pembayaran = $this->getFiltered($request);

        return view('kasir.laporan_penjualan', compact('pembayaran'));
    }

    /**
     * Download PDF laporan penjualan.
     */
    public function pdf(Request $request)
    {
        if (!$this->guardKasir($request)) {
            return redirect()->route('kasir.login');
        }

        $pembayaran    = $this->getFiltered($request);
        $dari          = $request->query('dari');
        $sampai        = $request->query('sampai');
        $statusFilter  = $request->query('status', 'semua');

        $pdf = Pdf::loadView('pdf.laporan_penjualan', compact(
            'pembayaran',
            'dari',
            'sampai',
            'statusFilter'
        ))->setPaper('A4', 'landscape');

        $filename = 'laporan-penjualan-' . now()->format('Ymd-His') . '.pdf';

        return $pdf->download($filename);
    }

    /**
     * Ambil data pembayaran dengan filter.
     */
    protected function getFiltered(Request $request)
    {
        $dari   = $request->query('dari');
        $sampai = $request->query('sampai');
        $status = $request->query('status', 'semua');

        $query = Pembayaran::with('pemesanan.Pelanggan')
            ->orderBy('id', 'desc');

        if ($dari && $sampai) {
            $query->where(function ($q) use ($dari, $sampai) {
                $q->whereBetween('tanggal_pembayaran', [
                        $dari . ' 00:00:00',
                        $sampai . ' 23:59:59',
                    ])
                  ->orWhereHas('pemesanan', fn($q2) =>
                      $q2->whereBetween('tanggal_pemesanan', [
                          $dari . ' 00:00:00',
                          $sampai . ' 23:59:59',
                      ])
                  );
            });
        } elseif ($dari) {
            $query->where(function ($q) use ($dari) {
                $q->where('tanggal_pembayaran', '>=', $dari . ' 00:00:00')
                  ->orWhereHas('pemesanan', fn($q2) =>
                      $q2->where('tanggal_pemesanan', '>=', $dari . ' 00:00:00')
                  );
            });
        } elseif ($sampai) {
            $query->where(function ($q) use ($sampai) {
                $q->where('tanggal_pembayaran', '<=', $sampai . ' 23:59:59')
                  ->orWhereHas('pemesanan', fn($q2) =>
                      $q2->where('tanggal_pemesanan', '<=', $sampai . ' 23:59:59')
                  );
            });
        }

        if ($status && $status !== 'semua') {
            $query->where('status_pembayaran', $status);
        }

        return $query->get();
    }
}
