<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice Pemesanan Seblak</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 14px;
            background-color: #f5f5f5;
            margin: 0;
            padding: 20px;
            color: #333;
        }
        .wrapper {
            max-width: 600px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }
        .header {
            background-color: #e65c00;
            padding: 24px 30px;
            color: #ffffff;
        }
        .header h1 {
            margin: 0;
            font-size: 22px;
            letter-spacing: 0.5px;
        }
        .header p {
            margin: 4px 0 0;
            font-size: 13px;
            opacity: 0.85;
        }
        .body {
            padding: 28px 30px;
        }
        .body p {
            margin: 0 0 12px;
            line-height: 1.6;
        }
        .info-box {
            background: #f9f9f9;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            padding: 16px 20px;
            margin: 20px 0;
        }
        .info-box table {
            width: 100%;
            border-collapse: collapse;
        }
        .info-box td {
            padding: 6px 0;
            font-size: 14px;
        }
        .info-box td:first-child {
            font-weight: bold;
            width: 45%;
            color: #555;
        }
        .badge {
            display: inline-block;
            padding: 2px 10px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: bold;
            background-color: #fef3c7;
            color: #92400e;
        }
        .badge.lunas {
            background-color: #d1fae5;
            color: #065f46;
        }
        .total-row td {
            font-size: 16px;
            font-weight: bold;
            color: #e65c00;
            padding-top: 10px !important;
            border-top: 1px solid #e5e7eb;
        }
        .attachment-note {
            background: #eff6ff;
            border-left: 4px solid #3b82f6;
            padding: 12px 16px;
            border-radius: 4px;
            margin: 20px 0;
            font-size: 13px;
            color: #1e40af;
        }
        .footer {
            background: #f9fafb;
            padding: 16px 30px;
            font-size: 12px;
            color: #9ca3af;
            border-top: 1px solid #e5e7eb;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="wrapper">

        {{-- HEADER --}}
        <div class="header">
            <h1>🍲 Pemesanan Seblak</h1>
            <p>Invoice Pembayaran Pesanan Anda</p>
        </div>

        {{-- BODY --}}
        <div class="body">

            <p>Halo, <strong>{{ $data['customer_name'] ?? 'Pelanggan' }}</strong>!</p>
            <p>Berikut adalah informasi pembayaran pesanan Anda:</p>

            {{-- INFO BOX --}}
            <div class="info-box">
                <table>
                    <tr>
                        <td>No Pembayaran</td>
                        <td>: {{ $data['id_pembayaran'] ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td>No Pesanan</td>
                        <td>: {{ $data['id_pemesanan'] ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td>Tanggal Pembayaran</td>
                        <td>: {{ $data['tanggal_pembayaran'] ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td>Metode Pembayaran</td>
                        <td>: {{ isset($data['metode_pembayaran']) ? ucfirst($data['metode_pembayaran']) : '-' }}</td>
                    </tr>
                    <tr>
                        <td>Status Pembayaran</td>
                        <td>:
                            <span class="badge {{ in_array($data['status_pembayaran'] ?? '', ['lunas', 'selesai', 'dibayar']) ? 'lunas' : '' }}">
                                {{ ucfirst($data['status_pembayaran'] ?? '-') }}
                            </span>
                        </td>
                    </tr>
                    <tr class="total-row">
                        <td>Total Pembayaran</td>
                        <td>: Rp {{ number_format($data['total_pembayaran'] ?? 0, 0, ',', '.') }}</td>
                    </tr>
                </table>
            </div>

            {{-- ATTACHMENT NOTE --}}
            <div class="attachment-note">
                📎 Invoice lengkap dalam format PDF terlampir pada email ini.
            </div>

            <p>Terima kasih telah memesan di <strong>Seblak Kami</strong>. Pesanan Anda sedang kami proses.</p>
            <p>Jika ada pertanyaan, silakan hubungi kami.</p>

        </div>

        {{-- FOOTER --}}
        <div class="footer">
            Email ini dikirim otomatis oleh sistem. Mohon tidak membalas email ini.<br>
            &copy; {{ date('Y') }} Pemesanan Seblak. All rights reserved.
        </div>

    </div>
</body>
</html>