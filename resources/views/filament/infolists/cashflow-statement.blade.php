@php
    $record  = $getRecord();
    $laporan = $record->laporan ?? ['aktivitas' => [], 'ringkasan' => []];

    // Periode -> Carbon untuk format header (samain dgn Jurnal Umum)
    try {
        $periodeCarbon = \Carbon\Carbon::parse($record->periode);
    } catch (\Throwable $e) {
        $periodeCarbon = \Carbon\Carbon::now();
    }

    // format angka: negatif jadi (1.000)
    $rp = function ($n) {
        $n = (float) $n;
        $txt = number_format(abs($n), 0, ',', '.');
        return $n < 0 ? '(' . $txt . ')' : $txt;
    };
@endphp

<div class="text-sm" style="background:#fff; padding:12px; border-radius:8px;">
    {{-- Header laporan: format mengikuti Jurnal Umum --}}
    <div class="mt-4 text-center bg-white py-2">
        <div class="text-sm uppercase">Seblak Sangkuriang</div>
        <div class="text-lg font-bold uppercase">Laporan Arus Kas</div>
        <div class="text-sm">Untuk periode berakhir pada tanggal {{ $periodeCarbon->copy()->endOfMonth()->translatedFormat('d F Y') }}</div>
        <div class="mt-1 font-semibold">Periode {{ $periodeCarbon->translatedFormat('F Y') }}</div>
    </div>

    <table class="w-full text-sm text-left border border-gray-200">
        <tbody>
            <tr class="bg-gray-100 font-semibold">
                <td class="px-4 py-2 border">Total kas di awal periode</td>
                <td class="px-4 py-2 border text-right">{{ $rp($record->saldo_awal) }}</td>
            </tr>

            @foreach ($laporan['aktivitas'] as $grup)
                <tr class="bg-gray-100 font-semibold">
                    <td colspan="2" class="px-4 py-2 border">{{ $grup['aktivitas'] }}</td>
                </tr>

                @if (!empty($grup['masuk']))
                    <tr><td colspan="2" class="px-4 py-2 border italic">Penerimaan kas dari:</td></tr>
                    @foreach ($grup['masuk'] as $item)
                        <tr>
                            <td class="px-4 py-2 border" style="padding-left:2rem;">{{ $item['label'] }}</td>
                            <td class="px-4 py-2 border text-right">{{ $rp($item['nilai']) }}</td>
                        </tr>
                    @endforeach
                @endif

                @if (!empty($grup['keluar']))
                    <tr><td colspan="2" class="px-4 py-2 border italic">Kas yang dibayarkan untuk:</td></tr>
                    @foreach ($grup['keluar'] as $item)
                        <tr>
                            <td class="px-4 py-2 border" style="padding-left:2rem;">{{ $item['label'] }}</td>
                            <td class="px-4 py-2 border text-right">{{ $rp(-abs($item['nilai'])) }}</td>
                        </tr>
                    @endforeach
                @endif

                <tr class="font-semibold">
                    <td class="px-4 py-2 border">Arus Kas Bersih</td>
                    <td class="px-4 py-2 border text-right">{{ $rp($grup['bersih']) }}</td>
                </tr>
            @endforeach

            <tr class="bg-gray-100 font-bold">
                <td class="px-4 py-2 border">Kenaikan / (Penurunan) Bersih Kas</td>
                <td class="px-4 py-2 border text-right">{{ $rp($laporan['ringkasan']['arus_bersih'] ?? 0) }}</td>
            </tr>
            <tr class="bg-gray-100 font-bold">
                <td class="px-4 py-2 border">Total kas di akhir periode</td>
                <td class="px-4 py-2 border text-right">{{ $rp($record->saldo_akhir) }}</td>
            </tr>
        </tbody>
    </table>
</div>
