<x-filament-widgets::widget>
    <x-filament::section>

        <h2 style="font-size:18px; font-weight:800; color:#111827; margin-bottom:16px;">Laporan Laba Rugi</h2>

        {{-- ── TOOLBAR ── --}}
        <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px; margin-bottom:20px;">

            <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
                <label style="font-size:13px; font-weight:600; color:#374151; white-space:nowrap;">Periode:</label>
                <input type="month"
                       wire:model="periode"
                       style="border:1.5px solid #d1d5db; border-radius:8px; padding:7px 12px; font-size:13px; color:#111827; outline:none; min-width:160px;">
                <button wire:click="filter"
                        style="display:inline-flex; align-items:center; gap:6px; background:linear-gradient(135deg,#2563eb,#1d4ed8); color:#fff; font-weight:600; font-size:13px; padding:8px 18px; border-radius:8px; border:none; cursor:pointer; box-shadow:0 2px 8px rgba(37,99,235,.35);">
                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/>
                    </svg>
                    Filter
                </button>
            </div>

            <a href="{{ route('laporan.laba-rugi.pdf', ['periode' => $periode]) }}"
               style="display:inline-flex; align-items:center; gap:6px; background:linear-gradient(135deg,#16a34a,#15803d); color:#fff; font-weight:600; font-size:13px; padding:8px 18px; border-radius:8px; text-decoration:none; box-shadow:0 2px 8px rgba(22,163,74,.35);">
                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v12m0 0l-4-4m4 4l4-4M21 12v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6"/>
                </svg>
                Unduh PDF
            </a>
        </div>

        @include('laporan.partials.report-header', ['company' => 'Seblak Sangkuriang', 'title' => 'Laporan Laba Rugi', 'periode' => $periode])

        {{-- ── EMPTY STATE ── --}}
        @if(!$hasData)
        <div style="text-align:center; padding:48px 20px; margin-top:16px; background:#fafafa; border-radius:12px; border:1.5px dashed #e5e7eb;">
            <div style="font-size:3rem; margin-bottom:12px;">📊</div>
            <div style="font-size:1rem; font-weight:700; color:#374151; margin-bottom:6px;">Tidak ada data di periode ini</div>
            <div style="font-size:.85rem; color:#6b7280;">
                Belum ada jurnal untuk periode <strong>{{ \Carbon\Carbon::createFromFormat('Y-m', $periode)->translatedFormat('F Y') }}</strong>.
            </div>
        </div>
        @else

        {{-- PENDAPATAN --}}
        <div style="margin-top:20px;">
            <div style="font-weight:700; font-size:14px; color:#111827; border-left:4px solid #16a34a; padding-left:10px; margin-bottom:10px;">Pendapatan</div>
            <table class="w-full text-sm border border-gray-200" style="border-collapse:collapse;">
                <thead style="background:#f0fdf4;">
                    <tr>
                        <th class="px-4 py-2 border" style="font-size:11px;text-transform:uppercase;color:#166534;text-align:left;">Kode</th>
                        <th class="px-4 py-2 border" style="font-size:11px;text-transform:uppercase;color:#166534;text-align:left;">Akun</th>
                        <th class="px-4 py-2 border text-right" style="font-size:11px;text-transform:uppercase;color:#166534;">Jumlah</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pendapatanGroups as $p)
                    <tr><td class="px-4 py-2 border">{{ $p['kode'] }}</td><td class="px-4 py-2 border">{{ $p['nama'] }}</td><td class="px-4 py-2 border text-right">{{ rupiah($p['jumlah']) }}</td></tr>
                    @empty
                    <tr><td colspan="3" class="px-4 py-2 border text-gray-400">Tidak ada pendapatan.</td></tr>
                    @endforelse
                </tbody>
                <tfoot><tr style="background:#f0fdf4;font-weight:700;"><td colspan="2" class="px-4 py-2 border text-right" style="color:#166534;">Total Pendapatan</td><td class="px-4 py-2 border text-right" style="color:#166534;">{{ rupiah($totalPendapatan) }}</td></tr></tfoot>
            </table>
        </div>

        {{-- BEBAN --}}
        <div style="margin-top:20px;">
            <div style="font-weight:700; font-size:14px; color:#111827; border-left:4px solid #dc2626; padding-left:10px; margin-bottom:10px;">Beban</div>
            <table class="w-full text-sm border border-gray-200" style="border-collapse:collapse;">
                <thead style="background:#fef2f2;">
                    <tr>
                        <th class="px-4 py-2 border" style="font-size:11px;text-transform:uppercase;color:#991b1b;text-align:left;">Kode</th>
                        <th class="px-4 py-2 border" style="font-size:11px;text-transform:uppercase;color:#991b1b;text-align:left;">Akun</th>
                        <th class="px-4 py-2 border text-right" style="font-size:11px;text-transform:uppercase;color:#991b1b;">Jumlah</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($bebanGroups as $b)
                    <tr><td class="px-4 py-2 border">{{ $b['kode'] }}</td><td class="px-4 py-2 border">{{ $b['nama'] }}</td><td class="px-4 py-2 border text-right">{{ rupiah($b['jumlah']) }}</td></tr>
                    @empty
                    <tr><td colspan="3" class="px-4 py-2 border text-gray-400">Tidak ada beban.</td></tr>
                    @endforelse
                </tbody>
                <tfoot><tr style="background:#fef2f2;font-weight:700;"><td colspan="2" class="px-4 py-2 border text-right" style="color:#991b1b;">Total Beban</td><td class="px-4 py-2 border text-right" style="color:#991b1b;">{{ rupiah($totalBeban) }}</td></tr></tfoot>
            </table>
        </div>

        {{-- LABA BERSIH --}}
        <div style="margin-top:20px; display:flex; justify-content:flex-end;">
            <div style="background:{{ $labaBersih >= 0 ? 'linear-gradient(135deg,#16a34a,#15803d)' : 'linear-gradient(135deg,#dc2626,#b91c1c)' }}; color:#fff; border-radius:12px; padding:16px 28px; text-align:right; min-width:280px; box-shadow:0 4px 12px rgba(0,0,0,.15);">
                <div style="font-size:11px; opacity:.8; text-transform:uppercase; letter-spacing:.5px;">Laba Bersih</div>
                <div style="font-size:24px; font-weight:900; margin-top:4px;">{{ rupiah($labaBersih) }}</div>
                @if($labaBersih < 0)<div style="font-size:11px; opacity:.8; margin-top:2px;">(Rugi)</div>@endif
            </div>
        </div>

        @endif

    </x-filament::section>
</x-filament-widgets::widget>
