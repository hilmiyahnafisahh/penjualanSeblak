@extends('layouts.app')

@section('content')
<div class="container mx-auto p-4">
    <div class="mb-4">
        <div class="flex items-center justify-between gap-4">
            <div>
                <form method="get" action="{{ route('laporan.laba-rugi') }}" class="flex items-center gap-2">
                    <label for="periode" class="whitespace-nowrap">Pilih Periode:</label>
                    <input type="month" name="periode" id="periode" value="{{ $periode ?? now()->format('Y-m') }}" class="border rounded px-2 py-1">
                    <button type="submit" class="ml-2 bg-green-500 text-black px-3 py-1 rounded">Filter</button>
                </form>
            </div>

            <div class="flex-shrink-0">
                <a href="{{ route('laporan.laba-rugi.pdf', ['periode' => $periode]) }}" class="inline-flex items-center" aria-label="Unduh PDF" style="background:#16a34a;color:#fff;padding:8px 14px;border-radius:8px;display:inline-flex;align-items:center;box-shadow:0 2px 6px rgba(0,0,0,0.08);text-decoration:none;">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="#fff">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v12m0 0l-4-4m4 4l4-4M21 12v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-6" />
                    </svg>
                    <span style="color:#fff;font-weight:600">Unduh PDF</span>
                </a>
            </div>
        </div>

        @include('laporan.partials.report-header', ['company' => 'Seblak Sangkuriang', 'title' => 'Laporan Laba Rugi', 'periode' => $periode])
    </div>
    <div class="mt-6 bg-white shadow rounded p-4">
            <div class="flex justify-between items-center">
            <h3 class="font-semibold">Pendapatan</h3>
            <a href="{{ route('laporan.laba-rugi.pdf', ['periode' => $periode]) }}" class="inline-flex items-center" aria-label="Unduh PDF" style="background:#16a34a;color:#fff;padding:8px 14px;border-radius:8px;display:inline-flex;align-items:center;box-shadow:0 2px 6px rgba(0,0,0,0.08);text-decoration:none;">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="#fff">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v12m0 0l-4-4m4 4l4-4M21 12v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-6" />
                </svg>
                <span style="color:#fff;font-weight:600">Unduh PDF</span>
            </a>
        </div>
        <table class="w-full text-sm mt-2 border">
            <thead class="bg-gray-100 text-xs uppercase">
                <tr>
                    <th class="px-4 py-2 border">Kode</th>
                    <th class="px-4 py-2 border">Akun</th>
                    <th class="px-4 py-2 border text-right">Jumlah</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pendapatanGroups as $p)
                <tr>
                    <td class="px-4 py-2 border">{{ $p['kode'] }}</td>
                    <td class="px-4 py-2 border">{{ $p['nama'] }}</td>
                    <td class="px-4 py-2 border text-right">{{ rupiah($p['jumlah']) }}</td>
                </tr>
                @empty
                <tr><td colspan="3" class="px-4 py-2">Tidak ada pendapatan.</td></tr>
                @endforelse
            </tbody>
        </table>

        <div class="mt-4 text-right font-semibold">Total Pendapatan: {{ rupiah($totalPendapatan) }}</div>
    </div>

    <div class="mt-6 bg-white shadow rounded p-4">
        <h3 class="font-semibold">Beban</h3>
        <table class="w-full text-sm mt-2 border">
            <thead class="bg-gray-100 text-xs uppercase">
                <tr>
                    <th class="px-4 py-2 border">Kode</th>
                    <th class="px-4 py-2 border">Akun</th>
                    <th class="px-4 py-2 border text-right">Jumlah</th>
                </tr>
            </thead>
            <tbody>
                @forelse($bebanGroups as $b)
                <tr>
                    <td class="px-4 py-2 border">{{ $b['kode'] }}</td>
                    <td class="px-4 py-2 border">{{ $b['nama'] }}</td>
                    <td class="px-4 py-2 border text-right">{{ rupiah($b['jumlah']) }}</td>
                </tr>
                @empty
                <tr><td colspan="3" class="px-4 py-2">Tidak ada beban.</td></tr>
                @endforelse
            </tbody>
        </table>

        <div class="mt-4 text-right font-semibold">Total Beban: {{ rupiah($totalBeban) }}</div>
    </div>

    <div class="mt-6 bg-white shadow rounded p-4 text-right">
        <h3 class="text-lg font-semibold">Laba Bersih: {{ rupiah($labaBersih) }}</h3>
    </div>
</div>
@endsection
