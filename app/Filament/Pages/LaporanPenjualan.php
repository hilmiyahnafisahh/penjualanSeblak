<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\Pemesanan;
use Carbon\Carbon;

class LaporanPenjualan extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected static ?string $navigationGroup = 'Laporan';
    protected static ?string $navigationLabel = 'Penjualan';
    protected static string $view = 'filament.pages.laporan-penjualan';

    public function getViewData(): array
    {
        $periodeType = request('periode_type', 'daily');
        if (!in_array($periodeType, ['daily', 'weekly', 'monthly'], true)) {
            $periodeType = 'daily';
        }

        $today = Carbon::now();
        $periodeDaily = request('periode_daily', $today->format('Y-m-d'));
        $periodeWeek = request('periode_week', $today->isoFormat('GGGG-[W]WW'));
        $periodeMonth = request('periode_month', $today->format('Y-m'));

        $periode = match ($periodeType) {
            'weekly' => $periodeWeek,
            'monthly' => $periodeMonth,
            default => $periodeDaily,
        };

        if ($periodeType === 'weekly' && str_contains($periode, '-W')) {
            [$year, $week] = explode('-W', $periode);
            $start = Carbon::now()->setISODate((int) $year, (int) $week)->startOfWeek(Carbon::MONDAY);
            $end = (clone $start)->endOfWeek(Carbon::SUNDAY);
        } elseif ($periodeType === 'monthly') {
            $start = Carbon::createFromFormat('Y-m', $periode)->startOfMonth();
            $end = Carbon::createFromFormat('Y-m', $periode)->endOfMonth();
        } else {
            $start = Carbon::createFromFormat('Y-m-d', $periode)->startOfDay();
            $end = (clone $start)->endOfDay();
        }

        $orders = Pemesanan::with(['DetailPesanan.menu', 'Pelanggan'])
            ->where('status_pemesanan', 'selesai')
            ->whereBetween('tanggal_pemesanan', [$start->startOfDay(), $end->endOfDay()])
            ->orderBy('tanggal_pemesanan')
            ->get();

        // ── Detail rows (tanggal | nama produk | qty | harga | total) ──
        // Termasuk topping sebagai baris terpisah
        $detailRows = [];
        // ── Per-item accumulator (menu + topping digabung by nama) ──
        $perItem = [];

        foreach ($orders as $order) {
            $orderDate = Carbon::parse($order->tanggal_pemesanan)->translatedFormat('d-m-Y');

            foreach ($order->DetailPesanan as $detail) {
                // ── Menu ──
                $menuName = $detail->menu?->nama_menu ?? 'Unknown';

                $unitPrice = (int) ($detail->menu?->harga_menu ?? 0);
                if ($unitPrice <= 0) $unitPrice = (int) ($detail->getRawOriginal('harga_menu') ?? 0);
                if ($unitPrice <= 0) $unitPrice = (int) ($detail->getRawOriginal('harga_jual') ?? 0);
                $qty   = (int) $detail->jumlah;
                // subtotal menu saja (tanpa topping)
                $menuSubtotal = $unitPrice * $qty;
                if ($menuSubtotal <= 0) $menuSubtotal = (int) $detail->subtotal;
                if ($unitPrice <= 0 && $qty > 0) $unitPrice = (int) round($menuSubtotal / $qty);

                $detailRows[] = [
                    'tanggal' => $orderDate,
                    'nama'    => $menuName,
                    'tipe'    => 'menu',
                    'jumlah'  => $qty,
                    'harga'   => $unitPrice,
                    'total'   => $menuSubtotal,
                ];

                if (!isset($perItem[$menuName])) {
                    $perItem[$menuName] = ['nama' => $menuName, 'tipe' => 'Menu', 'jumlah' => 0, 'total' => 0];
                }
                $perItem[$menuName]['jumlah'] += $qty;
                $perItem[$menuName]['total']  += $menuSubtotal;

                // ── Topping (dari JSON) ──
                $toppingList = is_array($detail->topping)
                    ? $detail->topping
                    : json_decode($detail->topping ?? '[]', true);

                foreach ($toppingList ?? [] as $top) {
                    $topNama  = $top['nama_barang'] ?? ($top['nama'] ?? 'Topping');
                    $topQty   = (int) ($top['qty'] ?? 0);
                    $topHarga = (int) ($top['harga'] ?? 0);
                    $topTotal = (int) ($top['subtotal'] ?? ($topQty * $topHarga));

                    if ($topQty <= 0) continue;

                    $detailRows[] = [
                        'tanggal' => $orderDate,
                        'nama'    => $topNama . ' (Topping)',
                        'tipe'    => 'topping',
                        'jumlah'  => $topQty,
                        'harga'   => $topHarga,
                        'total'   => $topTotal,
                    ];

                    $key = $topNama . '__topping';
                    if (!isset($perItem[$key])) {
                        $perItem[$key] = ['nama' => $topNama, 'tipe' => 'Topping', 'jumlah' => 0, 'total' => 0];
                    }
                    $perItem[$key]['jumlah'] += $topQty;
                    $perItem[$key]['total']  += $topTotal;
                }
            }
        }

        $reportRows  = collect($detailRows);
        $perItemRows = collect(array_values($perItem))->sortBy('nama')->values();

        $grouped = [];
        foreach ($orders as $order) {
            $orderDate    = Carbon::parse($order->tanggal_pemesanan)->translatedFormat('d-m-Y');
            $customerName = $order->Pelanggan?->nama_pelanggan ?? 'Unknown';
            foreach ($order->DetailPesanan as $detail) {
                $menuName  = $detail->menu?->nama_menu ?? 'Unknown';
                $unitPrice = (int) ($detail->menu?->harga_menu ?? 0);
                if ($unitPrice <= 0) $unitPrice = (int) ($detail->getRawOriginal('harga_menu') ?? 0);
                if ($unitPrice <= 0) $unitPrice = (int) ($detail->getRawOriginal('harga_jual') ?? 0);
                $qty   = (int) $detail->jumlah;
                $total = (int) $detail->subtotal;
                if ($unitPrice <= 0 && $qty > 0) $unitPrice = (int) round($total / $qty);
                $key = $orderDate . '|' . $menuName . '|' . $unitPrice;
                if (!isset($grouped[$key])) {
                    $grouped[$key] = ['nama' => $menuName, 'jumlah' => 0, 'total' => 0];
                }
                $grouped[$key]['jumlah'] += $qty;
                $grouped[$key]['total']  += $total;
            }
        }
        $topProducts = collect(array_values($grouped))
            ->groupBy('nama')
            ->map(fn($items, $name) => ['nama' => $name, 'jumlah' => $items->sum('jumlah'), 'total' => $items->sum('total')])
            ->sortByDesc('jumlah')
            ->values();

        $rangeText = match ($periodeType) {
            'daily' => 'Untuk tanggal ' . Carbon::createFromFormat('Y-m-d', $periode)->translatedFormat('d F Y'),
            'weekly' => 'Untuk minggu ' . $periode . ' (' . $start->translatedFormat('d F Y') . ' - ' . $end->translatedFormat('d F Y') . ')',
            'monthly' => 'Untuk periode ' . Carbon::createFromFormat('Y-m', $periode)->translatedFormat('F Y'),
            default => 'Periode laporan',
        };

        $periodeLabel = match ($periodeType) {
            'daily' => Carbon::createFromFormat('Y-m-d', $periode)->translatedFormat('d F Y'),
            'weekly' => $start->translatedFormat('d F Y') . ' - ' . $end->translatedFormat('d F Y'),
            'monthly' => Carbon::createFromFormat('Y-m', $periode)->translatedFormat('F Y'),
            default => Carbon::now()->translatedFormat('d F Y'),
        };

        return [
            'periodeType'  => $periodeType,
            'periodeDaily' => $periodeDaily,
            'periodeWeek'  => $periodeWeek,
            'periodeMonth' => $periodeMonth,
            'periode'      => $periode,
            'periodeLabel' => $periodeLabel,
            'rangeLabel'   => $rangeText,
            'reportRows'   => $reportRows,
            'perItemRows'  => $perItemRows,
            'topProducts'  => $topProducts,
            'topProduct'   => $topProducts->first(),
            'summary' => [
                'total_orders'  => $orders->count(),
                'total_qty'     => $reportRows->sum('jumlah'),
                'total_revenue' => $reportRows->sum('total'),
            ],
        ];
    }
}
