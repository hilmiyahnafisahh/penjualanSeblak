<!DOCTYPE html>
<html>

<head>

    <meta charset="utf-8">

    <title>Invoice Pemesanan</title>

    <style>

        body{
            font-family:sans-serif;
            font-size:12px;
        }

        table{
            width:100%;
            border-collapse:collapse;
            margin-top:10px;
            margin-bottom:25px;
        }

        table, th, td{
            border:1px solid black;
        }

        th, td{
            padding:8px;
        }

        .right{
            text-align:right;
        }

        .title{
            margin-top:30px;
        }

    </style>

</head>

<body>

@foreach($records as $record)

    <h2 class="title">

        Invoice Pemesanan

    </h2>

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

            <tr>

                <td>

                    {{ $detail->menu?->nama_menu ?? '-' }}

                </td>

                <td>

                    {{ $detail->jumlah }}

                </td>

                <td class="right">

                    Rp
                    {{ number_format(
                        $detail->harga_jual,
                        0,
                        ',',
                        '.'
                    ) }}

                </td>

                <td class="right">

                    Rp
                    {{ number_format(
                        $detail->subtotal,
                        0,
                        ',',
                        '.'
                    ) }}

                </td>

            </tr>

            {{-- TOPPING --}}

            @if(
                $detail->topping &&
                is_array($detail->topping)
            )

                @foreach($detail->topping as $top)

                    @php

                        $barang =
                            \App\Models\Barang::find(
                                $top['id_barang'] ?? null
                            );

                    @endphp

                    <tr>

                        <td>

                            ↳
                            {{ $barang?->nama_barang ?? 'Topping' }}

                        </td>

                        <td>

                            {{ $top['qty'] ?? 0 }}

                        </td>

                        <td class="right">

                            Rp

                            {{ number_format(
                                $top['harga'] ?? 0,
                                0,
                                ',',
                                '.'
                            ) }}

                        </td>

                        <td class="right">

                            Rp

                            {{ number_format(
                                $top['subtotal'] ?? 0,
                                0,
                                ',',
                                '.'
                            ) }}

                        </td>

                    </tr>

                @endforeach

            @endif

        @endforeach

        </tbody>

    </table>

    <h3 style="text-align:right">

        TOTAL :

        Rp
        {{ number_format(
            $record->subtotal,
            0,
            ',',
            '.'
        ) }}

    </h3>

    <hr>

@endforeach

</body>
</html>