<x-filament::page>
    <x-filament::card>
        <h1 class="text-2xl font-bold mb-4">Laporan Penjualan</h1>

        {{-- FILTER + TOMBOL PDF (satu baris, persis pola laba rugi) --}}
        <div class="flex justify-between items-center mb-4">
            <div>
                <form method="get" class="inline-flex flex-wrap gap-2 items-center" id="laporan-penjualan-form">
                    <label for="periode_type" class="font-semibold text-sm">Periode:</label>
                    <select id="periode_type" name="periode_type" class="border rounded px-2 py-1 text-sm">
                        <option value="daily"   {{ $periodeType === 'daily'   ? 'selected' : '' }}>Harian</option>
                        <option value="weekly"  {{ $periodeType === 'weekly'  ? 'selected' : '' }}>Mingguan</option>
                        <option value="monthly" {{ $periodeType === 'monthly' ? 'selected' : '' }}>Bulanan</option>
                    </select>

                    <input type="date"  name="periode_daily" id="periode_daily" value="{{ $periodeDaily }}"
                           class="border rounded px-2 py-1 text-sm {{ $periodeType !== 'daily'   ? 'hidden' : '' }}" />
                    <input type="week"  name="periode_week"  id="periode_week"  value="{{ $periodeWeek }}"
                           class="border rounded px-2 py-1 text-sm {{ $periodeType !== 'weekly'  ? 'hidden' : '' }}" />
                    <input type="month" name="periode_month" id="periode_month" value="{{ $periodeMonth }}"
                           class="border rounded px-2 py-1 text-sm {{ $periodeType !== 'monthly' ? 'hidden' : '' }}" />

                    <button type="submit"
                            style="background:#16a34a;color:#fff;padding:6px 14px;border-radius:6px;font-size:13px;font-weight:600;border:none;cursor:pointer;">
                        Filter
                    </button>
                </form>
            </div>
            <div>
                <a id="btn-unduh-pdf"
                   href="{{ route('laporan.penjualan.pdf', [
                       'periode_type'  => $periodeType,
                       'periode_daily' => $periodeDaily,
                       'periode_week'  => $periodeWeek,
                       'periode_month' => $periodeMonth,
                   ]) }}"
                   target="_blank"
                   style="background:#16a34a;color:#fff;padding:8px 14px;border-radius:8px;display:inline-flex;align-items:center;gap:6px;box-shadow:0 2px 6px rgba(0,0,0,0.08);text-decoration:none;font-weight:600;font-size:13px;">
                    <svg xmlns="http://www.w3.org/2000/svg" style="width:16px;height:16px;" fill="none" viewBox="0 0 24 24" stroke="#fff" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v12m0 0l-4-4m4 4l4-4M21 12v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-6" />
                    </svg>
                    <span style="color:#fff;">Unduh PDF</span>
                </a>
            </div>
        </div>

        <p class="text-sm text-gray-500 mb-4">{{ $rangeLabel }}</p>

        {{-- TABEL DETAIL PENJUALAN --}}
        <div class="bg-white p-4 rounded shadow border overflow-x-auto mb-4">
            <h2 class="text-lg font-semibold mb-3">Detail Penjualan</h2>
            <table class="min-w-full text-left text-sm border-collapse border">
                <thead class="bg-gray-100 text-xs uppercase">
                    <tr>
                        <th class="border px-3 py-2">Tanggal</th>
                        <th class="border px-3 py-2">Nama Produk</th>
                        <th class="border px-3 py-2">Tipe</th>
                        <th class="border px-3 py-2 text-right">Qty</th>
                        <th class="border px-3 py-2 text-right">Harga Satuan</th>
                        <th class="border px-3 py-2 text-right">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reportRows as $row)
                        <tr class="border-b hover:bg-gray-50">
                            <td class="border px-3 py-2">{{ $row['tanggal'] }}</td>
                            <td class="border px-3 py-2">{{ $row['nama'] }}</td>
                            <td class="border px-3 py-2">
                                @if($row['tipe'] === 'topping')
                                    <span style="background:#eff6ff;color:#1d4ed8;font-size:11px;padding:1px 7px;border-radius:10px;">Topping</span>
                                @else
                                    <span style="background:#f0fdf4;color:#15803d;font-size:11px;padding:1px 7px;border-radius:10px;">Menu</span>
                                @endif
                            </td>
                            <td class="border px-3 py-2 text-right">{{ $row['jumlah'] }}</td>
                            <td class="border px-3 py-2 text-right">{{ rupiah($row['harga']) }}</td>
                            <td class="border px-3 py-2 text-right">{{ rupiah($row['total']) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-3 py-4 text-center text-gray-500">
                                Tidak ada data penjualan untuk periode ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                @if($reportRows->isNotEmpty())
                <tfoot>
                    <tr style="background:#f0fdf4;font-weight:600;">
                        <td colspan="3" class="border px-3 py-2 text-right">Total</td>
                        <td class="border px-3 py-2 text-right">{{ $reportRows->sum('jumlah') }}</td>
                        <td class="border px-3 py-2"></td>
                        <td class="border px-3 py-2 text-right" style="color:#15803d;">{{ rupiah($reportRows->sum('total')) }}</td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>




    </x-filament::card>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const periodeType  = document.getElementById('periode_type');
            const dailyInput   = document.getElementById('periode_daily');
            const weeklyInput  = document.getElementById('periode_week');
            const monthlyInput = document.getElementById('periode_month');
            const pdfBtn       = document.getElementById('btn-unduh-pdf');
            const pdfBase      = "{{ route('laporan.penjualan.pdf') }}";

            const updateVisible = () => {
                const v = periodeType.value;
                dailyInput.classList.toggle('hidden',   v !== 'daily');
                weeklyInput.classList.toggle('hidden',  v !== 'weekly');
                monthlyInput.classList.toggle('hidden', v !== 'monthly');
                updatePdfHref();
            };

            const updatePdfHref = () => {
                const params = new URLSearchParams({
                    periode_type:  periodeType.value,
                    periode_daily: dailyInput.value,
                    periode_week:  weeklyInput.value,
                    periode_month: monthlyInput.value,
                });
                pdfBtn.href = pdfBase + '?' + params.toString();
            };

            periodeType.addEventListener('change',  updateVisible);
            dailyInput.addEventListener('change',   updatePdfHref);
            weeklyInput.addEventListener('change',  updatePdfHref);
            monthlyInput.addEventListener('change', updatePdfHref);
            updateVisible();
        });
    </script>
</x-filament::page>
