@props(['company' => 'Nama Perusahaan Jasa', 'title' => 'Laporan', 'periode' => null])
<div class="mt-4 text-center bg-white py-2">
    <div class="text-sm uppercase">{{ $company }}</div>
    <div class="text-lg font-bold uppercase">{{ $title }}</div>
    <div class="text-sm">Untuk periode berakhir pada tanggal {{ ($periode ? \Carbon\Carbon::createFromFormat('Y-m', $periode) : \Carbon\Carbon::now())->endOfMonth()->translatedFormat('d F Y') }}</div>
    <div class="mt-1 font-semibold">Periode {{ $periode ? \Carbon\Carbon::createFromFormat('Y-m', $periode)->translatedFormat('F Y') : \Carbon\Carbon::now()->translatedFormat('F Y') }}</div>
</div>
