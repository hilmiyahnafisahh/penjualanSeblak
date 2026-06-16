<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\JurnalDetail;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class LaporanController extends Controller
{
    public function labaRugi()
    {
        // Periode dari query string (format YYYY-MM) atau bulan berjalan
        $periode = request('periode');
        $start = $periode ? Carbon::createFromFormat('Y-m', $periode)->startOfMonth() : Carbon::now()->startOfMonth();
        $end = $periode ? Carbon::createFromFormat('Y-m', $periode)->endOfMonth() : Carbon::now()->endOfMonth();

        $details = JurnalDetail::with(['akun', 'jurnal'])
            ->whereHas('jurnal', function ($q) use ($start, $end) {
                $q->whereBetween('tgl', [$start->toDateString(), $end->toDateString()]);
            })->get();

        // Kelompokkan per akun pendapatan (jenis_akun = 'Pendapatan') dan jumlahkan credit
        $pendapatanGroups = $details->filter(fn($d) => $d->akun && $d->akun->jenis_akun === 'Pendapatan')
            ->groupBy(fn($d) => $d->akun->id)
            ->map(function ($items) {
                $akun = $items->first()->akun;
                return [
                    'kode' => $akun->kode_akun ?? '-',
                    'nama' => $akun->nama_akun ?? '-',
                    'jumlah' => $items->sum('credit'),
                ];
            })->values();

        // Kelompokkan per akun beban (jenis_akun = 'Beban') dan jumlahkan debit
        $bebanGroups = $details->filter(fn($d) => $d->akun && $d->akun->jenis_akun === 'Beban')
            ->groupBy(fn($d) => $d->akun->id)
            ->map(function ($items) {
                $akun = $items->first()->akun;
                return [
                    'kode' => $akun->kode_akun ?? '-',
                    'nama' => $akun->nama_akun ?? '-',
                    'jumlah' => $items->sum('debit'),
                ];
            })->values();

        $totalPendapatan = $pendapatanGroups->sum('jumlah');
        $totalBeban = $bebanGroups->sum('jumlah');
        $labaBersih = $totalPendapatan - $totalBeban;

        return view('laporan.laba-rugi', [
            'periode' => $periode,
            'periodeLabel' => $periode ? Carbon::createFromFormat('Y-m', $periode)->translatedFormat('F Y') : Carbon::now()->translatedFormat('F Y'),
            'pendapatanGroups' => $pendapatanGroups,
            'bebanGroups' => $bebanGroups,
            'totalPendapatan' => $totalPendapatan,
            'totalBeban' => $totalBeban,
            'labaBersih' => $labaBersih,
        ]);
    }

    public function pdf()
    {
        $periode = request('periode');
        $start = $periode ? Carbon::createFromFormat('Y-m', $periode)->startOfMonth() : Carbon::now()->startOfMonth();
        $end = $periode ? Carbon::createFromFormat('Y-m', $periode)->endOfMonth() : Carbon::now()->endOfMonth();

        $details = JurnalDetail::with(['akun', 'jurnal'])
            ->whereHas('jurnal', function ($q) use ($start, $end) {
                $q->whereBetween('tgl', [$start->toDateString(), $end->toDateString()]);
            })->get();

        $pendapatanGroups = $details->filter(fn($d) => $d->akun && $d->akun->jenis_akun === 'Pendapatan')
            ->groupBy(fn($d) => $d->akun->id)
            ->map(function ($items) {
                $akun = $items->first()->akun;
                return [
                    'kode' => $akun->kode_akun ?? '-',
                    'nama' => $akun->nama_akun ?? '-',
                    'jumlah' => $items->sum('credit'),
                ];
            })->values();

        $bebanGroups = $details->filter(fn($d) => $d->akun && $d->akun->jenis_akun === 'Beban')
            ->groupBy(fn($d) => $d->akun->id)
            ->map(function ($items) {
                $akun = $items->first()->akun;
                return [
                    'kode' => $akun->kode_akun ?? '-',
                    'nama' => $akun->nama_akun ?? '-',
                    'jumlah' => $items->sum('debit'),
                ];
            })->values();

        $totalPendapatan = $pendapatanGroups->sum('jumlah');
        $totalBeban = $bebanGroups->sum('jumlah');
        $labaBersih = $totalPendapatan - $totalBeban;

        $data = [
            'periode' => $periode,
            'periodeLabel' => $periode ? Carbon::createFromFormat('Y-m', $periode)->translatedFormat('F Y') : Carbon::now()->translatedFormat('F Y'),
            'pendapatanGroups' => $pendapatanGroups,
            'bebanGroups' => $bebanGroups,
            'totalPendapatan' => $totalPendapatan,
            'totalBeban' => $totalBeban,
            'labaBersih' => $labaBersih,
        ];

        $pdf = Pdf::loadView('laporan.laba-rugi-pdf', $data)->setPaper('A4', 'portrait');

        return $pdf->download('laba-rugi-' . ($periode ?? now()->format('Y-m')) . '.pdf');
    }

    public function penjualanPdf(Request $request)
    {
        $periodeType  = $request->query('periode_type', 'daily');
        if (!in_array($periodeType, ['daily', 'weekly', 'monthly'], true)) {
            $periodeType = 'daily';
        }

        $today        = Carbon::now();
        $periodeDaily = $request->query('periode_daily', $today->format('Y-m-d'));
        $periodeWeek  = $request->query('periode_week',  $today->isoFormat('GGGG-[W]WW'));
        $periodeMonth = $request->query('periode_month', $today->format('Y-m'));

        $periode = match ($periodeType) {
            'weekly'  => $periodeWeek,
            'monthly' => $periodeMonth,
            default   => $periodeDaily,
        };

        if ($periodeType === 'weekly' && str_contains($periode, '-W')) {
            [$year, $week] = explode('-W', $periode);
            $start = Carbon::now()->setISODate((int) $year, (int) $week)->startOfWeek(Carbon::MONDAY);
            $end   = (clone $start)->endOfWeek(Carbon::SUNDAY);
        } elseif ($periodeType === 'monthly') {
            $start = Carbon::createFromFormat('Y-m', $periode)->startOfMonth();
            $end   = Carbon::createFromFormat('Y-m', $periode)->endOfMonth();
        } else {
            $start = Carbon::createFromFormat('Y-m-d', $periode)->startOfDay();
            $end   = (clone $start)->endOfDay();
        }

        $orders = \App\Models\Pemesanan::with(['DetailPesanan.menu', 'Pelanggan'])
            ->where('status_pemesanan', 'selesai')
            ->whereBetween('tanggal_pemesanan', [$start->startOfDay(), $end->endOfDay()])
            ->orderBy('tanggal_pemesanan')
            ->get();

        $grouped = [];
        foreach ($orders as $order) {
            $orderDate    = Carbon::parse($order->tanggal_pemesanan)->translatedFormat('d-m-Y');
            $customerName = $order->Pelanggan?->nama_pelanggan ?? 'Unknown';

            foreach ($order->DetailPesanan as $detail) {
                $menuName  = $detail->menu?->nama_menu ?? 'Unknown';

                // Ambil harga satuan: prioritas harga_menu dari relasi,
                // fallback kolom DB mentah, fallback hitung dari subtotal/qty
                $unitPrice = (int) ($detail->menu?->harga_menu ?? 0);
                if ($unitPrice <= 0) {
                    $unitPrice = (int) ($detail->getRawOriginal('harga_menu') ?? 0);
                }
                if ($unitPrice <= 0) {
                    $unitPrice = (int) ($detail->getRawOriginal('harga_jual') ?? 0);
                }
                if ($unitPrice <= 0 && (int) $detail->jumlah > 0) {
                    $unitPrice = (int) round((int) $detail->subtotal / (int) $detail->jumlah);
                }

                $qty       = (int) $detail->jumlah;
                $total     = (int) $detail->subtotal;
                $key       = $orderDate . '|' . $customerName . '|' . $menuName . '|' . $unitPrice;

                if (!isset($grouped[$key])) {
                    $grouped[$key] = [
                        'tanggal'   => $orderDate,
                        'pelanggan' => $customerName,
                        'nama'      => $menuName,
                        'jumlah'    => 0,
                        'harga'     => $unitPrice,
                        'total'     => 0,
                    ];
                }

                $grouped[$key]['jumlah'] += $qty;
                $grouped[$key]['total']  += $total;
            }
        }

        $reportRows  = collect($grouped)->values()->sortBy([['tanggal', 'asc'], ['nama', 'asc']])->values();
        $topProducts = $reportRows
            ->groupBy('nama')
            ->map(fn ($items, $name) => [
                'nama'   => $name,
                'jumlah' => $items->sum('jumlah'),
                'total'  => $items->sum('total'),
            ])
            ->sortByDesc('jumlah')
            ->values();

        $rangeLabel = match ($periodeType) {
            'daily'   => 'Tanggal ' . Carbon::createFromFormat('Y-m-d', $periode)->translatedFormat('d F Y'),
            'weekly'  => 'Minggu ' . $periode . ' (' . $start->translatedFormat('d F Y') . ' – ' . $end->translatedFormat('d F Y') . ')',
            'monthly' => 'Periode ' . Carbon::createFromFormat('Y-m', $periode)->translatedFormat('F Y'),
            default   => 'Semua periode',
        };

        $data = [
            'rangeLabel'  => $rangeLabel,
            'reportRows'  => $reportRows,
            'topProducts' => $topProducts,
            'topProduct'  => $topProducts->first(),
            'summary'     => [
                'total_orders'  => $orders->count(),
                'total_qty'     => $reportRows->sum('jumlah'),
                'total_revenue' => $reportRows->sum('total'),
            ],
        ];

        $filename = 'laporan-penjualan-' . str_replace(['/', ' '], '-', $periode) . '.pdf';
        $pdf = Pdf::loadView('pdf.laporan_penjualan_admin', $data)->setPaper('A4', 'landscape');

        return $pdf->download($filename);
    }

    public function jurnalPdf(Request $request)    {
        $periode = $request->query('periode');
        $start = $periode ? Carbon::createFromFormat('Y-m', $periode)->startOfMonth() : Carbon::now()->startOfMonth();
        $end = $periode ? Carbon::createFromFormat('Y-m', $periode)->endOfMonth() : Carbon::now()->endOfMonth();

        $details = JurnalDetail::with(['akun', 'jurnal'])
            ->whereHas('jurnal', function ($q) use ($start, $end) {
                $q->whereBetween('tgl', [$start->toDateString(), $end->toDateString()]);
            })->get();

        $jurnals = $details->groupBy(fn($d) => $d->jurnal_id)->map(function ($items) {
            return $items->first()->jurnal->load('jurnaldetail.akun');
        })->values();

        $data = [
            'periodeLabel' => $periode ? Carbon::createFromFormat('Y-m', $periode)->translatedFormat('F Y') : Carbon::now()->translatedFormat('F Y'),
            'jurnals' => $jurnals,
        ];

        $pdf = Pdf::loadView('pdf.jurnal-umum-pdf', $data)->setPaper('A4', 'portrait');

        return $pdf->download('jurnal-umum-' . ($periode ?? now()->format('Y-m')) . '.pdf');
    }
}
