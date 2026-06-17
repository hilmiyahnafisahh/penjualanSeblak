<x-filament-widgets::widget>
    <x-filament::section>
        <div class="overflow-x-auto">

            {{-- ── TOOLBAR ── --}}
            <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px; margin-bottom:20px;">

                {{-- Filter periode --}}
                <form wire:submit.prevent="filterJurnal"
                      style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;">

                    <label for="periode"
                           style="font-size:13px; font-weight:600; color:#374151; white-space:nowrap;">
                        Periode:
                    </label>

                    <input type="month"
                           wire:model="periode"
                           id="periode"
                           style="border:1.5px solid #d1d5db; border-radius:8px; padding:7px 12px; font-size:13px; color:#111827; outline:none; min-width:160px;">

                    <button type="submit"
                            style="display:inline-flex; align-items:center; gap:6px; background:linear-gradient(135deg,#2563eb,#1d4ed8); color:#fff; font-weight:600; font-size:13px; padding:8px 18px; border-radius:8px; border:none; cursor:pointer; box-shadow:0 2px 8px rgba(37,99,235,.35); transition:opacity .15s;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/>
                        </svg>
                        Filter
                    </button>
                </form>

                {{-- Unduh PDF --}}
                <a href="{{ route('laporan.jurnal-umum.pdf', ['periode' => $periode]) }}"
                   style="display:inline-flex; align-items:center; gap:6px; background:linear-gradient(135deg,#16a34a,#15803d); color:#fff; font-weight:600; font-size:13px; padding:8px 18px; border-radius:8px; text-decoration:none; box-shadow:0 2px 8px rgba(22,163,74,.35);">
                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v12m0 0l-4-4m4 4l4-4M21 12v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6"/>
                    </svg>
                    Unduh PDF
                </a>
            </div>

            @include('laporan.partials.report-header', [
                'company' => 'Seblak Sangkuriang',
                'title'   => 'Jurnal Umum',
                'periode' => $periode,
            ])

            {{-- ── TABEL ── --}}
            <table class="w-full text-sm text-left border border-gray-200" style="margin-top:16px;">
                <thead style="background:#f3f4f6;">
                    <tr>
                        <th class="px-4 py-2 border" style="font-size:11px; text-transform:uppercase; color:#6b7280;">No.</th>
                        <th class="px-4 py-2 border" style="font-size:11px; text-transform:uppercase; color:#6b7280;">Tanggal</th>
                        <th class="px-4 py-2 border" style="font-size:11px; text-transform:uppercase; color:#6b7280;">Akun</th>
                        <th class="px-4 py-2 border" style="font-size:11px; text-transform:uppercase; color:#6b7280;">Reff</th>
                        <th class="px-4 py-2 border text-right" style="font-size:11px; text-transform:uppercase; color:#6b7280;">Debet</th>
                        <th class="px-4 py-2 border text-right" style="font-size:11px; text-transform:uppercase; color:#6b7280;">Kredit</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($jurnals as $jurnal)
                        @foreach($jurnal->jurnaldetail as $detail)
                            <tr style="{{ $loop->even ? 'background:#f9fafb;' : '' }}">
                                <td class="px-4 py-2 border">{{ $loop->iteration }}</td>
                                <td class="px-4 py-2 border">{{ \Carbon\Carbon::parse($jurnal->tgl)->format('Y-m-d') }}</td>

                                @if($detail->debit != 0)
                                    <td class="px-4 py-2 border">{{ optional($detail->akun)->nama_akun ?? '-' }}</td>
                                    <td class="px-4 py-2 border">{{ $jurnal->no_referensi }}</td>
                                    <td class="px-4 py-2 border text-right">{{ rupiah($detail->debit) }}</td>
                                @else
                                    <td class="px-4 py-2 border" style="padding-left:2rem;">{{ optional($detail->akun)->nama_akun ?? '-' }}</td>
                                    <td class="px-4 py-2 border">{{ $jurnal->no_referensi }}</td>
                                    <td class="px-4 py-2 border text-right"></td>
                                @endif

                                @if($detail->credit != 0)
                                    <td class="px-4 py-2 border text-right">{{ rupiah($detail->credit) }}</td>
                                @else
                                    <td class="px-4 py-2 border text-right"></td>
                                @endif
                            </tr>
                        @endforeach
                    @endforeach
                </tbody>
                <tfoot>
                    <tr style="background:#f3f4f6; font-weight:700;">
                        <td colspan="4" class="text-right px-4 py-2 border">Total</td>
                        <td class="text-right px-4 py-2 border">{{ rupiah($jurnals->flatMap->jurnaldetail->sum('debit')) }}</td>
                        <td class="text-right px-4 py-2 border">{{ rupiah($jurnals->flatMap->jurnaldetail->sum('credit')) }}</td>
                    </tr>
                </tfoot>
            </table>

        </div>
    </x-filament::section>
</x-filament-widgets::widget>
