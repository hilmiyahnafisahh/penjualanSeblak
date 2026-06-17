<?php

namespace App\Services;

use App\Models\Cashflow;
use App\Models\Pembayaran;
use App\Models\CatatBeban;
use App\Models\penggajian as Penggajian; // perhatikan: nama class-nya huruf kecil
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class CashflowAi
{
    public function analisa(string $periode): Cashflow
    {
        [$tahun, $bulan] = explode('-', $periode);

        // ===== KAS MASUK (Operasional) =====
        $penerimaanPenjualan = (float) Pembayaran::whereYear('tanggal_pembayaran', $tahun)
            ->whereMonth('tanggal_pembayaran', $bulan)
            ->where('status_pembayaran', 'lunas')
            ->sum('total_pembayaran');

        // ===== KAS KELUAR (Operasional) =====
        // a) Gaji karyawan -> tabel penggajian (yang sudah Dibayarkan)
        $gajiKaryawan = (float) Penggajian::whereYear('tanggal_penggajian', $tahun)
            ->whereMonth('tanggal_penggajian', $bulan)
            ->where('status', 'Dibayarkan')
            ->sum('nominal');

        // b) Pembelian ke vendor -> tabel pembayaran_barang
        $pembelianVendor = (float) DB::table('pembayaran_barang')
            ->whereYear('tgl_bayar', $tahun)
            ->whereMonth('tgl_bayar', $bulan)
            ->sum('jumlah_bayar');

        // c) Beban lain -> catat_beban, dikelompokkan per jenis_beban
        $bebanPerJenis = CatatBeban::whereYear('tanggal', $tahun)
            ->whereMonth('tanggal', $bulan)
            ->selectRaw('jenis_beban, SUM(total) as jumlah')
            ->groupBy('jenis_beban')
            ->pluck('jumlah', 'jenis_beban')
            ->toArray();

        // ===== Susun Laporan Arus Kas (3 aktivitas) =====
        $laporan = $this->bangunLaporan(
            $penerimaanPenjualan,
            $gajiKaryawan,
            $pembelianVendor,
            $bebanPerJenis
        );

        $totalMasuk  = $laporan['ringkasan']['total_masuk'];
        $totalKeluar = $laporan['ringkasan']['total_keluar'];
        $arusBersih  = $laporan['ringkasan']['arus_bersih'];

        // Saldo awal = saldo akhir dari periode sebelumnya yang sudah dianalisa
        $saldoAwal  = $this->saldoSebelum($periode);
        $saldoAkhir = $saldoAwal + $arusBersih;

        // ===== Tanya Gemini =====
        $hasil = $this->tanyaGemini(
            $periode, $totalMasuk, $totalKeluar, $arusBersih, $bebanPerJenis
        );

        // ===== Simpan / perbarui (1 baris per periode) =====
        return Cashflow::updateOrCreate(
            ['periode' => $periode],
            [
                'total_masuk'      => $totalMasuk,
                'total_keluar'     => $totalKeluar,
                'arus_bersih'      => $arusBersih,
                'saldo_awal'       => $saldoAwal,
                'saldo_akhir'      => $saldoAkhir,
                'laporan'          => $laporan,
                'status_kesehatan' => $hasil['status_kesehatan'] ?? 'Tidak diketahui',
                'ringkasan'        => $hasil['ringkasan']        ?? null,
                'rekomendasi'      => $hasil['rekomendasi']      ?? [],
                'proyeksi'         => $hasil['proyeksi']         ?? null,
                'raw_response'     => $hasil['raw']              ?? null,
            ]
        );
    }

    /**
     * Susun data jadi 3 aktivitas arus kas.
     * Ubah $petaAktivitas untuk memindahkan jenis_beban tertentu
     * ke Investasi / Pendanaan. Selain yang terdaftar -> Operasional.
     */
    protected function bangunLaporan(
        float $penerimaanPenjualan,
        float $gajiKaryawan,
        float $pembelianVendor,
        array $bebanPerJenis
    ): array {
        // PETA KATEGORI (key = jenis_beban huruf kecil)
        $petaAktivitas = [
            'pembelian aset'      => 'Aktivitas Investasi',
            'pembelian peralatan' => 'Aktivitas Investasi',
            'beli kendaraan'      => 'Aktivitas Investasi',
            'pinjaman bank'       => 'Aktivitas Pendanaan',
            'bayar pinjaman'      => 'Aktivitas Pendanaan',
            'setoran modal'       => 'Aktivitas Pendanaan',
            'prive'               => 'Aktivitas Pendanaan',
        ];

        $aktivitas = [
            'Aktivitas Operasional' => ['masuk' => [], 'keluar' => []],
            'Aktivitas Investasi'   => ['masuk' => [], 'keluar' => []],
            'Aktivitas Pendanaan'   => ['masuk' => [], 'keluar' => []],
        ];

        // Baris tetap Operasional
        if ($penerimaanPenjualan > 0) {
            $aktivitas['Aktivitas Operasional']['masuk'][] = [
                'label' => 'Penerimaan dari penjualan', 'nilai' => $penerimaanPenjualan,
            ];
        }
        if ($gajiKaryawan > 0) {
            $aktivitas['Aktivitas Operasional']['keluar'][] = [
                'label' => 'Gaji karyawan', 'nilai' => $gajiKaryawan,
            ];
        }
        if ($pembelianVendor > 0) {
            $aktivitas['Aktivitas Operasional']['keluar'][] = [
                'label' => 'Pembelian bahan / inventaris', 'nilai' => $pembelianVendor,
            ];
        }

        // Beban dinamis dari catat_beban
        foreach ($bebanPerJenis as $jenis => $jumlah) {
            $jumlah = (float) $jumlah;
            if ($jumlah == 0) {
                continue;
            }
            $kategori = $petaAktivitas[strtolower(trim($jenis))] ?? 'Aktivitas Operasional';
            $aktivitas[$kategori]['keluar'][] = ['label' => $jenis, 'nilai' => $jumlah];
        }

        // Subtotal per aktivitas
        $totalMasuk = 0;
        $totalKeluar = 0;
        $hasil = [];
        foreach ($aktivitas as $nama => $grup) {
            $masuk  = array_sum(array_column($grup['masuk'], 'nilai'));
            $keluar = array_sum(array_column($grup['keluar'], 'nilai'));
            $totalMasuk  += $masuk;
            $totalKeluar += $keluar;
            $hasil[] = [
                'aktivitas' => $nama,
                'masuk'     => $grup['masuk'],
                'keluar'    => $grup['keluar'],
                'bersih'    => $masuk - $keluar,
            ];
        }

        return [
            'aktivitas' => $hasil,
            'ringkasan' => [
                'total_masuk'  => $totalMasuk,
                'total_keluar' => $totalKeluar,
                'arus_bersih'  => $totalMasuk - $totalKeluar,
            ],
        ];
    }

    /**
     * Saldo awal = saldo akhir dari periode SEBELUMNYA yang sudah dianalisa.
     * Format periode "YYYY-MM" bisa dibandingkan langsung sebagai string,
     * jadi tidak perlu konversi tanggal. Kalau belum ada periode
     * sebelumnya, hasilnya 0.
     */
    protected function saldoSebelum(string $periode): float
    {
        return (float) Cashflow::where('periode', '<', $periode)
            ->orderByDesc('periode')
            ->value('saldo_akhir');
    }

    /** Kirim ringkasan angka ke Gemini, minta balasan JSON. */
    protected function tanyaGemini(
        string $periode,
        float $masuk,
        float $keluar,
        float $bersih,
        array $rincianBeban
    ): array {
        $apiKey = config('services.gemini.key');
        $model  = config('services.gemini.model', 'gemini-2.5-flash');

        $fmt = fn ($n) => 'Rp ' . number_format($n, 0, ',', '.');

        $rincianText = empty($rincianBeban)
            ? '- (tidak ada beban tercatat)'
            : collect($rincianBeban)
                ->map(fn ($v, $k) => "- {$k}: " . $fmt($v))
                ->implode("\n");

        $masukStr  = $fmt($masuk);
        $keluarStr = $fmt($keluar);
        $bersihStr = $fmt($bersih);

        $prompt = "Kamu adalah konsultan keuangan untuk UMKM kuliner Seblak.\n"
            . "Analisis arus kas untuk periode {$periode}:\n\n"
            . "- Total Kas Masuk : {$masukStr}\n"
            . "- Total Kas Keluar: {$keluarStr}\n"
            . "- Arus Kas Bersih : {$bersihStr}\n\n"
            . "Rincian beban:\n{$rincianText}\n\n"
            . "Jawab HANYA dengan JSON valid (tanpa markdown, tanpa teks lain) berformat:\n"
            . '{"status_kesehatan":"Sehat|Waspada|Kritis",'
            . '"ringkasan":"maksimal 3 kalimat",'
            . '"rekomendasi":["saran 1","saran 2","saran 3"],'
            . '"proyeksi":"perkiraan kondisi bulan depan 1-2 kalimat"}';

        $url = "https://generativelanguage.googleapis.com/v1/models/"
            . $model . ":generateContent?key=" . $apiKey;

        $response = Http::timeout(60)->post($url, [
            'contents' => [
                ['parts' => [['text' => $prompt]]],
            ],
            'generationConfig' => [
                'temperature' => 0.4,
            ],
        ]);

        if ($response->failed()) {
            throw new \RuntimeException('Gagal menghubungi Gemini: ' . $response->body());
        }

        $teks = $response->json('candidates.0.content.parts.0.text', '');
        $teks = trim(preg_replace('/^```(json)?|```$/m', '', $teks));

        if (preg_match('/\{.*\}/s', $teks, $m)) {
            $teks = $m[0];
        }

        $data = json_decode($teks, true) ?: [];
        $data['raw'] = $teks;

        return $data;
    }

    /**
 * Chat multi-giliran dengan Gemini.
 * $riwayat = array of ['role' => 'user'|'model', 'text' => '...'].
 */
public function chat(string $pertanyaan, array $riwayat = []): string
{
    $apiKey = config('services.gemini.key');
    $model  = config('services.gemini.model', 'gemini-2.5-flash');

    // Konteks ringkas dari analisa terbaru biar jawaban relevan
    $konteks = $this->konteksData();

    $contents = [];

    // "Pesan sistem" disisipkan sebagai giliran pertama
    $contents[] = [
        'role'  => 'user',
        'parts' => [['text' =>
            "Kamu asisten keuangan untuk toko seblak & mukena. "
            . "Jawab ringkas, ramah, dalam Bahasa Indonesia. "
            . "Berikut data keuangan terbaru sebagai acuan:\n" . $konteks
        ]],
    ];
    $contents[] = [
        'role'  => 'model',
        'parts' => [['text' => 'Siap! Aku bantu jawab soal keuangan tokomu.']],
    ];

    // Riwayat percakapan sebelumnya
    foreach ($riwayat as $m) {
        $contents[] = [
            'role'  => ($m['role'] ?? 'user') === 'model' ? 'model' : 'user',
            'parts' => [['text' => $m['text'] ?? '']],
        ];
    }

    // Pertanyaan terbaru
    $contents[] = ['role' => 'user', 'parts' => [['text' => $pertanyaan]]];

    $url = "https://generativelanguage.googleapis.com/v1/models/"
        . $model . ":generateContent?key=" . $apiKey;

    $response = Http::timeout(60)->post($url, [
        'contents'         => $contents,
        'generationConfig' => ['temperature' => 0.7],
    ]);

    if ($response->failed()) {
        return 'Maaf, lagi ada gangguan menghubungi AI. Coba lagi sebentar ya.';
    }

    return $response->json(
        'candidates.0.content.parts.0.text',
        'Maaf, aku belum bisa menjawab sekarang.'
    );
}

/** Ringkasan data keuangan terbaru untuk konteks chat. */
protected function konteksData(): string
{
    $last = Cashflow::orderByDesc('periode')->first();

    if (! $last) {
        return "Belum ada data analisa arus kas yang tersimpan.";
    }

    $masuk  = 'Rp ' . number_format((float) $last->total_masuk, 0, ',', '.');
    $keluar = 'Rp ' . number_format((float) $last->total_keluar, 0, ',', '.');
    $bersih = 'Rp ' . number_format((float) $last->arus_bersih, 0, ',', '.');

    return "Periode {$last->periode} | kas masuk {$masuk}, "
        . "kas keluar {$keluar}, arus bersih {$bersih}, "
        . "status {$last->status_kesehatan}.";
}
}