<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;
use Phpml\Clustering\KMeans;
// use Phpml\Preprocessing\Normalizer;
// use Phpml\Preprocessing\MinMaxScaler;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;

class CustomerClustering extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-presentation-chart-line';
    protected static string $view = 'filament.pages.customer-clustering';
    protected static ?string $navigationGroup = 'Analisis';

    public $axisX = 0;
    public $axisY = 1;

    // =========================
    // FORM (TANPA LIVE)
    // =========================
    protected function getFormSchema(): array
    {
        return [
            Select::make('axisX')
                ->label('Sumbu X')
                ->options([
                    0 => 'Total Tagihan',
                    1 => 'Total Qty',
                    2 => 'Total Laba'
                ])
                ->statePath('axisX')
                ->dehydrateStateUsing(fn ($state) => (int) $state) // 🔥 ini kunci
                ->required(),

            Select::make('axisY')
                ->label('Sumbu Y')
                ->options([
                    0 => 'Total Tagihan',
                    1 => 'Total Qty',
                    2 => 'Total Laba'
                ])
                ->statePath('axisY')
                ->dehydrateStateUsing(fn ($state) => (int) $state) // 🔥 ini kunci
                ->required(),
        ];
    }

    // =========================
    // CLUSTERING
    // =========================
    public function getAllCharts()
    {
        // Gunakan tabel seblak: pelanggan, pemesanan, detail_pemesanan
        $data = DB::table('pelanggan')
            ->join('pemesanan', 'pelanggan.id', '=', 'pemesanan.id_pelanggan')
            ->join('detail_pemesanan', 'pemesanan.id', '=', 'detail_pemesanan.id_pemesanan')
            ->select(
                'pelanggan.nama_pelanggan',
                DB::raw('SUM(pemesanan.subtotal) as total_tagihan'),
                DB::raw('SUM(detail_pemesanan.jumlah) as total_qty'),
                DB::raw('SUM(detail_pemesanan.subtotal * 0.4) as total_laba')
            )
            ->groupBy('pelanggan.id', 'pelanggan.nama_pelanggan')
            ->get();

        if ($data->isEmpty()) return [
            'chart1' => ['datasets' => []],
            'chart2' => ['datasets' => []],
            'chart3' => ['datasets' => []],
        ];

        $originalData = $data->values()->all();

        // === BUILD SAMPLE ===
        $samples = [];
        foreach ($originalData as $i => $row) {
            $samples[$i] = [
                (float)$row->total_tagihan,
                (float)$row->total_qty,
                (float)$row->total_laba
            ];
        }

        // KMeans butuh minimal sebanyak jumlah cluster
        $k = min(3, count($originalData));
        if ($k < 2) return [
            'chart1' => ['datasets' => []],
            'chart2' => ['datasets' => []],
            'chart3' => ['datasets' => []],
        ];

        // === MIN MAX SCALING ===
        $this->minMaxScale($samples);

        // === KMEANS ===
        $kmeans = new KMeans($k);
        $clusters = $kmeans->cluster($samples);

        // === BUILD 3 CHART ===
        return [
            'chart1' => $this->formatChart($clusters, $originalData, 0, 1),
            'chart2' => $this->formatChart($clusters, $originalData, 0, 2),
            'chart3' => $this->formatChart($clusters, $originalData, 1, 2),
        ];
    }

    private function minMaxScale(&$samples)
    {
        $numFeatures = count($samples[0]);

        $mins = array_fill(0, $numFeatures, INF);
        $maxs = array_fill(0, $numFeatures, -INF);

        // cari min & max tiap kolom
        foreach ($samples as $sample) {
            foreach ($sample as $i => $value) {
                if ($value < $mins[$i]) $mins[$i] = $value;
                if ($value > $maxs[$i]) $maxs[$i] = $value;
            }
        }

        // scaling
        foreach ($samples as &$sample) {
            foreach ($sample as $i => &$value) {
                if ($maxs[$i] - $mins[$i] == 0) {
                    $value = 0; // hindari pembagian 0
                } else {
                    $value = ($value - $mins[$i]) / ($maxs[$i] - $mins[$i]);
                }
            }
        }
    }

    // =========================
    // FORMAT CHART
    // =========================
    private function formatChart($clusters, $originalData, $axisX, $axisY)
    {
        $datasets = [];
        $colors = ['#FF6384', '#36A2EB', '#4BC0C0'];
        $columns = ['total_tagihan', 'total_qty', 'total_laba'];

        $colX = $columns[$axisX];
        $colY = $columns[$axisY];

        foreach ($clusters as $index => $cluster) {

            $points = [];

            foreach ($cluster as $key => $sample) {
                if (isset($originalData[$key])) {
                    $row = $originalData[$key];

                    $points[] = [
                        'x' => (float)$row->$colX,
                        'y' => (float)$row->$colY,
                        'label' => $row->nama_pelanggan,
                    ];
                }
            }

            $datasets[] = [
                'label' => 'Cluster ' . ($index + 1),
                'data' => $points,
                'backgroundColor' => $colors[$index],
            ];
        }

        return ['datasets' => $datasets];
    }

    // =========================
    // DEFAULT LOAD
    // =========================
    protected function getViewData(): array
    {
        $charts = $this->getAllCharts();

        return [
            'chart1' => $charts['chart1'] ?? ['datasets' => []],
            'chart2' => $charts['chart2'] ?? ['datasets' => []],
            'chart3' => $charts['chart3'] ?? ['datasets' => []],
        ];
    }

    // =========================
    // SUBMIT BUTTON TRIGGER
    // =========================
    public function submit()
    {
        // 🔥 pastikan ambil state terbaru dari form
        $this->form->getState();

        $data = $this->getClusteringData(3);
        // dd($data);
        // optional debug (tidak menghentikan program)
        // logger($data);

        $this->dispatch('updateChart', [
            'chartData' => $data,
            'axisX' => (int) $this->axisX,
            'axisY' => (int) $this->axisY,
        ]);
    }

    
}