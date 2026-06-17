<x-filament-widgets::widget>
    <x-filament::section>
        <div class="overflow-x-auto">

            {{-- ── TOOLBAR ── --}}
            <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px; margin-bottom:20px;">

                <div>
                    <div style="font-size:18px; font-weight:700; color:#111827;">Buku Besar</div>
                    <div style="font-size:12px; color:#6b7280; margin-top:2px;">Transaksi jurnal dikelompokkan per akun</div>
                </div>

                {{-- Unduh PDF --}}
                <a href="{{ route('laporan.buku-besar.pdf') }}"
                   style="display:inline-flex; align-items:center; gap:6px; background:linear-gradient(135deg,#16a34a,#15803d); color:#fff; font-weight:600; font-size:13px; padding:8px 18px; border-radius:8px; text-decoration:none; box-shadow:0 2px 8px rgba(22,163,74,.35);">
                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v12m0 0l-4-4m4 4l4-4M21 12v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6"/>
                    </svg>
                    Unduh PDF
                </a>
            </div>

            {{-- ── AKUN GROUPS ── --}}
            @forelse($groupedDetails as $akunId => $details)
                @php
                    $firstDetail = $details->first();
                    $akun        = $firstDetail->akun;
                    $totalDebit  = $details->sum('debit');
                    $totalCredit = $details->sum('credit');
                @endphp

                <div style="margin-bottom:20px; border-radius:10px; overflow:hidden; border:1px solid #e5e7eb; box-shadow:0 1px 4px rgba(0,0,0,.06);">
                    {{-- Akun header --}}
                    <div style="background:linear-gradient(90deg,#1e40af,#2563eb); padding:10px 16px;">
                        <span style="color:#fff; font-weight:700; font-size:13px;">
                            {{ $akun->kode_akun ?? '-' }} — {{ $akun->nama_akun ?? 'Tanpa Akun' }}
                        </span>
                    </div>

                    <table class="w-full text-sm text-left text-gray-700">
                        <thead style="background:#f8fafc;">
                            <tr>
                                <th class="px-3 py-2 border" style="font-size:11px; text-transform:uppercase; color:#6b7280;">Tanggal</th>
                                <th class="px-3 py-2 border" style="font-size:11px; text-transform:uppercase; color:#6b7280;">ID Jurnal</th>
                                <th class="px-3 py-2 border" style="font-size:11px; text-transform:uppercase; color:#6b7280;">Ref</th>
                                <th class="px-3 py-2 border" style="font-size:11px; text-transform:uppercase; color:#6b7280;">Deskripsi</th>
                                <th class="px-3 py-2 border text-right" style="font-size:11px; text-transform:uppercase; color:#6b7280;">Debet</th>
                                <th class="px-3 py-2 border text-right" style="font-size:11px; text-transform:uppercase; color:#6b7280;">Kredit</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($details as $detail)
                                <tr style="{{ $loop->even ? 'background:#f9fafb;' : '' }}">
                                    <td class="px-3 py-2 border">{{ optional($detail->jurnal)->tgl ? \Carbon\Carbon::parse($detail->jurnal->tgl)->format('Y-m-d') : '-' }}</td>
                                    <td class="px-3 py-2 border">{{ optional($detail->jurnal)->id ?? '-' }}</td>
                                    <td class="px-3 py-2 border">{{ optional($detail->jurnal)->no_referensi ?? '-' }}</td>
                                    <td class="px-3 py-2 border">{{ $detail->deskripsi ?? optional($detail->jurnal)->deskripsi ?? '-' }}</td>
                                    <td class="px-3 py-2 border text-right">{{ $detail->debit ? rupiah($detail->debit) : '' }}</td>
                                    <td class="px-3 py-2 border text-right">{{ $detail->credit ? rupiah($detail->credit) : '' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr style="background:#eff6ff; font-weight:700; font-size:12px;">
                                <td colspan="4" class="px-3 py-2 border text-right" style="text-transform:uppercase; color:#1e40af;">
                                    Total {{ $akun->kode_akun ?? '' }}
                                </td>
                                <td class="px-3 py-2 border text-right" style="color:#1e40af;">{{ rupiah($totalDebit) }}</td>
                                <td class="px-3 py-2 border text-right" style="color:#1e40af;">{{ rupiah($totalCredit) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            @empty
                <div style="padding:24px; text-align:center; color:#6b7280; font-size:14px;">
                    Tidak ada transaksi jurnal untuk ditampilkan.
                </div>
            @endforelse

        </div>
    </x-filament::section>
</x-filament-widgets::widget>
