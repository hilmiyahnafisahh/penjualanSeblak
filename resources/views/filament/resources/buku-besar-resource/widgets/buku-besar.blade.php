<x-filament-widgets::widget>
    <x-filament::section>
        <div class="overflow-x-auto">
            <div class="mb-6 text-center">
                <div class="text-lg font-semibold">Buku Besar</div>
                <div class="text-sm text-gray-600">Laporan transaksi jurnal umum, dikelompokkan berdasarkan akun</div>
            </div>

            @foreach($groupedDetails as $akunId => $details)
                @php
                    $firstDetail = $details->first();
                    $akun = $firstDetail->akun;
                    $totalDebit = $details->sum('debit');
                    $totalCredit = $details->sum('credit');
                @endphp

                <div class="mb-4 bg-white border border-gray-200 shadow-sm">
                    <div class="px-4 py-3 bg-gray-100 border-b border-gray-200">
                        <div class="font-semibold">{{ $akun->kode_akun ?? 'Akun Tidak Diketahui' }} - {{ $akun->nama_akun ?? 'Tanpa Akun' }}</div>
                    </div>

                    <table class="w-full text-sm text-left text-gray-700">
                        <thead class="bg-gray-50 text-xs uppercase text-gray-600">
                            <tr>
                                <th class="px-3 py-2 border">Tanggal</th>
                                <th class="px-3 py-2 border">ID Jurnal</th>
                                <th class="px-3 py-2 border">Ref</th>
                                <th class="px-3 py-2 border">Deskripsi</th>
                                <th class="px-3 py-2 border text-right">Debet</th>
                                <th class="px-3 py-2 border text-right">Kredit</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($details as $detail)
                                <tr class="even:bg-gray-50">
                                    <td class="px-3 py-2 border">{{ optional($detail->jurnal)->tgl ? \Carbon\Carbon::parse($detail->jurnal->tgl)->format('Y-m-d') : '-' }}</td>
                                    <td class="px-3 py-2 border">{{ optional($detail->jurnal)->id ?? '-' }}</td>
                                    <td class="px-3 py-2 border">{{ optional($detail->jurnal)->no_referensi ?? '-' }}</td>
                                    <td class="px-3 py-2 border">{{ $detail->deskripsi ?? optional($detail->jurnal)->deskripsi ?? '-' }}</td>
                                    <td class="px-3 py-2 border text-right">{{ $detail->debit ? rupiah($detail->debit) : '' }}</td>
                                    <td class="px-3 py-2 border text-right">{{ $detail->credit ? rupiah($detail->credit) : '' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-gray-100 font-semibold text-xs uppercase">
                            <tr>
                                <td colspan="4" class="px-3 py-2 border text-right">Total {{ $akun->kode_akun ?? '' }}</td>
                                <td class="px-3 py-2 border text-right">{{ rupiah($totalDebit) }}</td>
                                <td class="px-3 py-2 border text-right">{{ rupiah($totalCredit) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            @endforeach

            @if($groupedDetails->isEmpty())
                <div class="p-4 text-sm text-gray-600">Tidak ada transaksi jurnal untuk ditampilkan.</div>
            @endif
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
