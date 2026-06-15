83% penyimpanan digunakan … Jika ruang penyimpanan sudah penuh, Anda tidak dapat membuat, mengedit, dan mengupload file. Dapatkan penyimpanan 30 GB seharga Rp 3.500 selama 2 bulan Rp 14.500.
<x-filament-widgets::widget>
    <x-filament::section>
        
        <div class="overflow-x-auto">

            <!-- Filter Periode Jurnal -->
            <!-- Akhir Filter Periode Jurnal-->

            <!-- Tambahan filter -->
            <div class="row">

          
            <form wire:submit.prevent="filterJurnal">
                <label for="periode">Pilih Periode:</label>
                <input type="month" wire:model="periode" id="periode" class="border rounded px-2 py-1">
                <button type="submit" class="ml-2 bg-green-500 text-black px-3 py-1 rounded">Filter</button>
            </form>

            
                <br><br>
               
                <div class="col-sm-12" style="background-color:white;" align="center">
                    <b>Seblak Sangkuriang</b><br>
                    <b>Jurnal Umum</b><br>
                    <b>Periode {{ $periode ? \Carbon\Carbon::createFromFormat('Y-m', $periode)->translatedFormat('F Y') : now()->translatedFormat('F Y') }} </b><br>
                </div>
               <br>
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