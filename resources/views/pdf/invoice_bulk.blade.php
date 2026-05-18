<!DOCTYPE html>
<html>

<head>

    <meta charset="utf-8">

    <title>Pemesanan Seblak</title>

    <style>

        body {
            font-family: sans-serif;
            font-size: 12px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            margin-bottom: 25px;
        }

        table, th, td {
            border: 1px solid black;
        }

        th, td {
            padding: 8px;
        }

        .right {
            text-align: right;
        }

        .title {
            margin-top: 30px;
        }

        .topping-name {
            padding-left: 24px;
            color: #444;
        }

        .topping-row td {
            background-color: #f9f9f9;
        }

    </style>

</head>

<body>

@foreach($records as $record)

    <h2 class="title">Pemesanan Seblak</h2>

    <p>
        <strong>No Pesanan :</strong>
        {{ $record->id_pesanan }}
    </p>

    <p>
        <strong>Pelanggan :</strong>
        {{ $record->pelanggan->nama_pelanggan ?? '-' }}
    </p>

    <p>
        <strong>Tanggal :</strong>
        {{ $record->tanggal_pemesanan }}
    </p>

    <table>

        <thead>
            <tr>
                <th>Menu</th>
                <th>Qty</th>
                <th>Harga</th>
                <th>Subtotal</th>
            </tr>
        </thead>

        <tbody>

        @foreach($record->DetailPesanan as $detail)

            @php
                $jumlah = $detail->jumlah ?? 1;

                // Fallback harga: harga_jual → menu->harga → subtotal/jumlah → 0
                $hargaJual = $detail->harga_jual ?? 0;

                if ($hargaJual == 0) {
                    $hargaJual = $detail->menu?->harga ?? 0;
                }

                if ($hargaJual == 0 && $jumlah > 0) {
                    $hargaJual = ($detail->subtotal ?? 0) / $jumlah;
                }

                // Fallback subtotal: subtotal → harga × jumlah
                $subtotalMenu = $detail->subtotal ?? 0;
                if ($subtotalMenu == 0) {
                    $subtotalMenu = $hargaJual * $jumlah;
                }
            @endphp

            {{-- Baris menu utama --}}
            <tr>
                <td>{{ $detail->menu?->nama_menu ?? '-' }}</td>
                <td>{{ $jumlah }}</td>
                <td class="right">
                    Rp {{ number_format($hargaJual, 0, ',', '.') }}
                </td>
                <td class="right">
                    Rp {{ number_format($subtotalMenu, 0, ',', '.') }}
                </td>
            </tr>

            {{-- Baris topping (jika ada) --}}
            @if(!empty($detail->topping) && is_array($detail->topping))

                @foreach($detail->topping as $top)

                    @php
                        $idBarang        = $top['id_barang'] ?? null;
                        $barang          = $idBarang ? \App\Models\Barang::find($idBarang) : null;
                        $namaTopping     = $barang?->nama_barang ?? 'Topping';
                        $qtyTopping      = $top['qty']      ?? 0;
                        $hargaTopping    = $top['harga']    ?? 0;
                        $subtotalTopping = $top['subtotal'] ?? ($qtyTopping * $hargaTopping);
                    @endphp

                    <tr class="topping-row">
                        <td class="topping-name">+ {{ $namaTopping }}</td>
                        <td>{{ $qtyTopping }}</td>
                        <td class="right">
                            Rp {{ number_format($hargaTopping, 0, ',', '.') }}
                        </td>
                        <td class="right">
                            Rp {{ number_format($subtotalTopping, 0, ',', '.') }}
                        </td>
                    </tr>

                @endforeach

            @endif

        @endforeach

        </tbody>

    </table>

    {{-- Grand total: subtotal semua menu + subtotal semua topping --}}
    @php
        $grandTotal = 0;

        foreach ($record->DetailPesanan as $detail) {

            $jumlah = $detail->jumlah ?? 1;

            // Harga fallback
            $hargaJual = $detail->harga_jual ?? 0;
            if ($hargaJual == 0) {
                $hargaJual = $detail->menu?->harga ?? 0;
            }
            if ($hargaJual == 0 && $jumlah > 0) {
                $hargaJual = ($detail->subtotal ?? 0) / $jumlah;
            }

            // Subtotal fallback
            $subtotalMenu = $detail->subtotal ?? 0;
            if ($subtotalMenu == 0) {
                $subtotalMenu = $hargaJual * $jumlah;
            }

            $grandTotal += $subtotalMenu;

            // Topping
            if (!empty($detail->topping) && is_array($detail->topping)) {
                foreach ($detail->topping as $top) {
                    $qty   = $top['qty']   ?? 0;
                    $harga = $top['harga'] ?? 0;
                    $grandTotal += $top['subtotal'] ?? ($qty * $harga);
                }
            }
        }
    @endphp

    <h3 style="text-align: right;">
        TOTAL : Rp {{ number_format($grandTotal, 0, ',', '.') }}
    </h3>

    <hr>

@endforeach

</body>
</html>