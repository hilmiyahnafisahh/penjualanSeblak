<?php

namespace App\Services;

use App\Models\LaporanPenjualanAi;
use App\Models\Pemesanan;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;

class LaporanPenjualanAiService
{
    /**
     * Hitung data penjualan untuk periode, minta analisis Gemini, simpan & return model.
     */
    public function analisa(string $periodeType, string $periodeValue): LaporanPenjualanAi
    {
        // ── Hitung rentang tanggal ──
        [$start, $end] = $this->hitungRentang($periodeType, $periodeValue);

        // ── Ambil pesanan selesai dalam rentang ──
        $orders = Pemesanan::with(['DetailPesanan.menu'])
            ->where('status_pemesanan', 'selesai')
            ->whereBetween('tanggal_pemesanan', [$start, $end])
            ->orderBy('tanggal_pemesanan')
            ->get();

        // ── Susun data ──
        $detailRows = [];
        $perItem    = [];

        foreach ($orders as $order) {
            $tgl = Carbon::parse($order->tanggal_pemesanan)->translatedFormat('d-m-Y');

            foreach ($order->DetailPesanan as $detail) {
                $menuName  = $detail->menu?->nama_menu ?? 'Unknown';
                $unitPrice = (int) ($detail->menu?->harga_menu ?? 0);
                if ($unitPrice <= 0) $unitPrice = (int) ($detail->getRawOriginal('harga_menu') ?? 0);
                if ($unitPrice <= 0) $unitPrice = (int) ($detail->getRawOriginal('harga_jual') ?? 0);
                $qty          = (int) $detail->jumlah;
                $menuSubtotal = $unitPrice * $qty ?: (int) $detail->subtotal;
                if ($unitPrice <= 0 && $qty > 0) $unitPrice = (int) round($menuSubtotal / $qty);

                $detailRows[] = ['tanggal' => $tgl, 'nama' => $menuName, 'tipe' => 'menu',
                                 'jumlah' => $qty, 'harga' => $unitPrice, 'total' => $menuSubtotal];

                $perItem[$menuName] = $perItem[$menuName] ?? ['nama' => $menuName, 'tipe' => 'Menu', 'jumlah' => 0, 'total' => 0];
                $perItem[$menuName]['jumlah'] += $qty;
                $perItem[$menuName]['total']  += $menuSubtotal;

                // Topping
                $toppingList = is_array($detail->topping) ? $detail->topping
                    : json_decode($detail->topping ?? '[]', true);

                foreach ($toppingList ?? [] as $top) {
                    $topNama  = $top['nama_barang'] ?? ($top['nama'] ?? 'Topping');
                    $topQty   = (int) ($top['qty'] ?? 0);
                    $topHarga = (int) ($top['harga'] ?? 0);
                    $topTotal = (int) ($top['subtotal'] ?? ($topQty * $topHarga));
                    if ($topQty <= 0) continue;

                    $detailRows[] = ['tanggal' => $tgl, 'nama' => $topNama . ' (Topping)',
                                     'tipe' => 'topping', 'jumlah' => $topQty,
                                     'harga' => $topHarga, 'total' => $topTotal];

                    $key = $topNama . '__topping';
                    $perItem[$key] = $perItem[$key] ?? ['nama' => $topNama, 'tipe' => 'Topping', 'jumlah' => 0, 'total' => 0];
                    $perItem[$key]['jumlah'] += $topQty;
                    $perItem[$key]['total']  += $topTotal;
                }
            }
        }

        $rows        = collect($detailRows);
        $perItemColl = collect(array_values($perItem));
        $topMenu     = $perItemColl->where('tipe', 'Menu')->sortByDesc('jumlah')->values()->take(5)->all();
        $topTopping  = $perItemColl->where('tipe', 'Topping')->sortByDesc('jumlah')->values()->take(5)->all();

        $totalPendapatan = $rows->sum('total');
        $totalQty        = $rows->sum('jumlah');

        // ── Minta Gemini ──
        $hasilAi = $this->tanyaGemini(
            $periodeType, $periodeValue,
            $orders->count(), $totalQty, $totalPendapatan,
            $topMenu, $topTopping
        );

        // ── Simpan / perbarui ──
        return LaporanPenjualanAi::updateOrCreate(
            ['periode' => $periodeValue, 'tipe_periode' => $periodeType],
            [
                'total_pesanan'    => $orders->count(),
                'total_qty'        => $totalQty,
                'total_pendapatan' => $totalPendapatan,
                'top_menu'         => $topMenu,
                'top_topping'      => $topTopping,
                'detail_rows'      => array_values($detailRows),
                'status_penjualan' => $hasilAi['status_penjualan'] ?? null,
                'ringkasan'        => $hasilAi['ringkasan']        ?? null,
                'rekomendasi'      => $hasilAi['rekomendasi']      ?? [],
                'proyeksi'         => $hasilAi['proyeksi']         ?? null,
                'raw_response'     => $hasilAi['raw']              ?? null,
            ]
        );
    }

    protected function hitungRentang(string $type, string $value): array
    {
        if ($type === 'weekly' && str_contains($value, '-W')) {
            [$year, $week] = explode('-W', $value);
            $start = Carbon::now()->setISODate((int) $year, (int) $week)->startOfWeek(Carbon::MONDAY)->startOfDay();
            $end   = (clone $start)->endOfWeek(Carbon::SUNDAY)->endOfDay();
        } elseif ($type === 'monthly') {
            $start = Carbon::createFromFormat('Y-m', $value)->startOfMonth()->startOfDay();
            $end   = Carbon::createFromFormat('Y-m', $value)->endOfMonth()->endOfDay();
        } else {
            $start = Carbon::createFromFormat('Y-m-d', $value)->startOfDay();
            $end   = (clone $start)->endOfDay();
        }
        return [$start, $end];
    }

    protected function tanyaGemini(
        string $type, string $value,
        int $totalPesanan, int $totalQty, float $totalPendapatan,
        array $topMenu, array $topTopping
    ): array {
        $apiKey = config('services.gemini.key');

        // Urutan model fallback: coba satu per satu kalau 503
        $models = [
            config('services.gemini.model', 'gemini-2.5-flash'),
            'gemini-1.5-flash',
            'gemini-1.5-pro',
        ];

        $fmt = fn($n) => 'Rp ' . number_format($n, 0, ',', '.');

        $menuText = empty($topMenu) ? '(tidak ada data)'
            : collect($topMenu)->map(fn($m) => "- {$m['nama']}: {$m['jumlah']} terjual ({$fmt($m['total'])})")->implode("\n");
        $toppingText = empty($topTopping) ? '(tidak ada data)'
            : collect($topTopping)->map(fn($t) => "- {$t['nama']}: {$t['jumlah']} terjual ({$fmt($t['total'])})")->implode("\n");

        $prompt = "Kamu adalah analis bisnis kuliner untuk UMKM Seblak Sangkuriang.\n"
            . "Analisis data penjualan periode {$type} = {$value}:\n\n"
            . "- Total Pesanan  : {$totalPesanan}\n"
            . "- Total Item Terjual: {$totalQty}\n"
            . "- Total Pendapatan  : {$fmt($totalPendapatan)}\n\n"
            . "Menu terlaris:\n{$menuText}\n\n"
            . "Topping favorit:\n{$toppingText}\n\n"
            . "Jawab HANYA dengan JSON valid (tanpa markdown, tanpa teks lain) berformat:\n"
            . '{"status_penjualan":"Tinggi|Sedang|Rendah",'
            . '"ringkasan":"maksimal 3 kalimat analisis kondisi penjualan",'
            . '"rekomendasi":["saran 1","saran 2","saran 3"],'
            . '"proyeksi":"perkiraan tren penjualan ke depan 1-2 kalimat"}';

        $lastError = null;

        foreach ($models as $model) {
            $url = "https://generativelanguage.googleapis.com/v1/models/{$model}:generateContent?key={$apiKey}";

            // Coba maksimal 3x per model dengan backoff
            for ($attempt = 1; $attempt <= 3; $attempt++) {
                try {
                    $response = \Illuminate\Support\Facades\Http::timeout(60)->post($url, [
                        'contents'         => [['parts' => [['text' => $prompt]]]],
                        'generationConfig' => ['temperature' => 0.4],
                    ]);

                    $status = $response->status();

                    // 503 / 429 = server sibuk, tunggu lalu retry
                    if (in_array($status, [503, 429])) {
                        $lastError = $response->body();
                        if ($attempt < 3) {
                            sleep($attempt * 2); // 2s, 4s
                        }
                        continue;
                    }

                    if ($response->failed()) {
                        $lastError = $response->body();
                        break; // error lain, coba model berikutnya
                    }

                    // Sukses — parse JSON
                    $teks = trim(preg_replace('/^```(json)?|```$/m', '', $response->json('candidates.0.content.parts.0.text', '')));
                    if (preg_match('/\{.*\}/s', $teks, $m)) $teks = $m[0];

                    $data        = json_decode($teks, true) ?: [];
                    $data['raw'] = $teks;
                    return $data;

                } catch (\Throwable $e) {
                    $lastError = $e->getMessage();
                    if ($attempt < 3) sleep($attempt * 2);
                }
            }
        }

        throw new \RuntimeException('Gagal menghubungi Gemini setelah beberapa percobaan: ' . $lastError);
    }
}
