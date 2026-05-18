<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembayaran Pesanan</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>

<body class="bg-gray-100 min-h-screen flex items-center justify-center">

<div class="bg-white p-8 rounded-lg shadow-md w-full max-w-2xl">

    <h1 class="text-2xl font-bold mb-6 text-center">
        Pembayaran Pesanan
    </h1>

    {{-- DATA PESANAN --}}
    <div class="mb-6">
        <h2 class="text-lg font-semibold mb-2">Data Pesanan</h2>

        <div class="bg-gray-50 p-4 rounded">

            <p>
                <strong>Nama Pelanggan:</strong>
                {{ optional($pemesanan->pelanggan)->nama_pelanggan ?? '-' }}
            </p>

            <p>
                <strong>No Pesanan:</strong>
                {{ $pemesanan->id_pesanan ?? $pemesanan->id }}
            </p>

            <p>
                <strong>Tanggal:</strong>
                {{ $pemesanan->tanggal_pemesanan
                    ? \Carbon\Carbon::parse($pemesanan->tanggal_pemesanan)->format('d/m/Y H:i')
                    : '-' }}
            </p>

        </div>
    </div>

    {{-- DETAIL MENU --}}
    <div class="mb-6">
        <h2 class="text-lg font-semibold mb-2">Detail Menu</h2>

        <div class="bg-gray-50 p-4 rounded">

            <table class="w-full text-sm">
                <thead>
                <tr class="border-b">
                    <th class="text-left py-2">Menu</th>
                    <th class="text-center py-2">Qty</th>
                    <th class="text-right py-2">Subtotal</th>
                </tr>
                </thead>

                <tbody>
                @forelse($pemesanan->detailPesanan ?? [] as $detail)
                    <tr class="border-b">
                        <td class="py-2">
                            {{ optional($detail->menu)->nama_menu ?? 'Menu tidak ditemukan' }}
                        </td>

                        <td class="text-center py-2">
                            {{ $detail->jumlah ?? 0 }}
                        </td>

                        <td class="text-right py-2">
                            Rp {{ number_format($detail->subtotal ?? 0, 0, ',', '.') }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="text-center py-4 text-gray-500">
                            Tidak ada detail pesanan
                        </td>
                    </tr>
                @endforelse
                </tbody>

                <tfoot>
                <tr class="font-bold">
                    <td colspan="2" class="text-right py-2">Total:</td>
                    <td class="text-right py-2">
                        Rp {{ number_format($pemesanan->subtotal ?? 0, 0, ',', '.') }}
                    </td>
                </tr>
                </tfoot>

            </table>

        </div>
    </div>

    {{-- FORM PEMBAYARAN --}}
    <form action="{{ route('pembayaran.store', $pemesanan->id) }}" method="POST">
        @csrf

        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">
                Metode Pembayaran
            </label>

            <select name="metode_pembayaran"
                    class="w-full p-2 border border-gray-300 rounded-md"
                    required>

                <option value="cash">Tunai</option>
                <option value="qris">QRIS</option>
                <option value="transfer">Transfer</option>

            </select>
        </div>

        <button type="submit"
                class="w-full bg-blue-500 text-white py-2 px-4 rounded-md hover:bg-blue-600">
            Bayar Sekarang
        </button>

    </form>

</div>

</body>
</html>