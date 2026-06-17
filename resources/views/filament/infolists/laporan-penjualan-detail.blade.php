@php $rows = collect($getState() ?? []); @endphp
@if($rows->isEmpty())
    <p class="text-sm text-gray-400 py-4 text-center">Tidak ada detail penjualan.</p>
@else
    <div class="overflow-x-auto">
        <table class="min-w-full text-sm border-collapse border">
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
                @foreach($rows as $row)
                    <tr class="border-b hover:bg-gray-50">
                        <td class="border px-3 py-2">{{ $row['tanggal'] }}</td>
                        <td class="border px-3 py-2">{{ $row['nama'] }}</td>
                        <td class="border px-3 py-2">
                            @if(($row['tipe'] ?? '') === 'topping')
                                <span style="background:#eff6ff;color:#1d4ed8;font-size:11px;padding:2px 8px;border-radius:10px;">Topping</span>
                            @else
                                <span style="background:#f0fdf4;color:#15803d;font-size:11px;padding:2px 8px;border-radius:10px;">Menu</span>
                            @endif
                        </td>
                        <td class="border px-3 py-2 text-right">{{ $row['jumlah'] }}</td>
                        <td class="border px-3 py-2 text-right">Rp {{ number_format($row['harga'], 0, ',', '.') }}</td>
                        <td class="border px-3 py-2 text-right">Rp {{ number_format($row['total'], 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr style="background:#f0fdf4;font-weight:600;">
                    <td colspan="3" class="border px-3 py-2 text-right">Total</td>
                    <td class="border px-3 py-2 text-right">{{ $rows->sum('jumlah') }}</td>
                    <td class="border px-3 py-2"></td>
                    <td class="border px-3 py-2 text-right" style="color:#15803d;">
                        Rp {{ number_format($rows->sum('total'), 0, ',', '.') }}
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>
@endif
