<x-filament-widgets::widget>
    <x-filament::section>
        
        <div class="overflow-x-auto">

            <!-- Filter Periode Jurnal -->
            <!-- Akhir Filter Periode Jurnal-->

            <!-- Tambahan filter -->
            <div class="mb-4">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <form wire:submit.prevent="filterJurnal" class="flex items-center gap-2">
                            <label for="periode" class="whitespace-nowrap">Pilih Periode:</label>
                            <input type="month" wire:model="periode" id="periode" class="border rounded px-2 py-1">
                            <button type="submit" class="ml-2 bg-green-500 text-black px-3 py-1 rounded">Filter</button>
                        </form>
                    </div>

                    <div class="flex-shrink-0">
                        <a href="{{ route('laporan.jurnal-umum.pdf', ['periode' => $periode]) }}" class="inline-flex items-center" aria-label="Unduh PDF" style="background:#16a34a;color:#fff;padding:8px 14px;border-radius:8px;display:inline-flex;align-items:center;box-shadow:0 2px 6px rgba(0,0,0,0.08);text-decoration:none;">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="#fff">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v12m0 0l-4-4m4 4l4-4M21 12v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-6" />
                            </svg>
                            <span style="color:#fff;font-weight:600">Unduh PDF</span>
                        </a>
                    </div>
                </div>

                @include('laporan.partials.report-header', ['company' => 'Seblak Sangkuriang', 'title' => 'Jurnal Umum', 'periode' => $periode])
            </div>
            <!-- Akhir Tambahan Filter -->

            <table class="w-full text-sm text-left border border-gray-200">
                <thead class="bg-gray-100 text-xs uppercase">
                    <tr>
                        <th class="px-4 py-2 border">No.</th>
                        <th class="px-4 py-2 border">Tanggal</th>
                        <th class="px-4 py-2 border">Akun</th>
                        <th class="px-4 py-2 border">Reff</th>
                        <th class="px-4 py-2 border">Debet</th>
                        <th class="px-4 py-2 border">Kredit</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($jurnals as $jurnal)
                        @foreach($jurnal->jurnaldetail as $detail)
                            <tr>
                                <td class="px-4 py-2 border">{{ $loop->iteration }}</td>
                                <td class="px-4 py-2 border">{{ \Carbon\Carbon::parse($jurnal->tgl)->format('Y-m-d') }}</td>
                                
                                {{-- Hanya tampilkan kolom jika debit ≠ 0 --}}
                                @if($detail->debit != 0)
                                    <td class="px-4 py-2 border">{{ optional($detail->akun)->nama_akun ?? '-' }}</td>
                                    <td class="px-4 py-2 border">{{ $jurnal->no_referensi }}</td>
                                    <td class="px-4 py-2 border text-right">{{ rupiah($detail->debit) }}</td>
                                @else
                                    <td class="px-4 py-2 border">&nbsp;&nbsp;&nbsp;{{ optional($detail->akun)->nama_akun ?? '-' }}</td>
                                    <td class="px-4 py-2 border">{{ $jurnal->no_referensi }}</td>
                                    <td class="px-4 py-2 border text-right"></td>
                                @endif

                                {{-- Hanya tampilkan kolom jika credit ≠ 0 --}}
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
                    <tr class="font-semibold bg-gray-100">
                        <td colspan="4" class="text-right px-4 py-2 border">Total</td>
                        <td class="text-right px-4 py-2 border">
                            {{ rupiah($jurnals->flatMap->jurnaldetail->sum('debit')) }}
                        </td>
                        <td class="text-right px-4 py-2 border">
                            {{ rupiah($jurnals->flatMap->jurnaldetail->sum('credit')) }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>

    </x-filament::section>
</x-filament-widgets::widget>