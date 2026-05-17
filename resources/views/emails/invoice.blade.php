<h2>Invoice Pembayaran</h2>
<p>Halo {{ $data['customer_name'] ?? 'Pelanggan' }},</p>
<p>Berikut informasi pembayaran Anda:</p>
<ul>
    <li><strong>No Pembayaran:</strong> {{ $data['id_pembayaran'] ?? '-' }}</li>
    <li><strong>No Pesanan:</strong> {{ $data['id_pemesanan'] ?? '-' }}</li>
    <li><strong>Tanggal Pembayaran:</strong> {{ $data['tanggal_pembayaran'] ?? '-' }}</li>
    <li><strong>Metode Pembayaran:</strong> {{ isset($data['metode_pembayaran']) ? ucfirst($data['metode_pembayaran']) : '-' }}</li>
    <li><strong>Status Pembayaran:</strong> {{ $data['status_pembayaran'] ?? '-' }}</li>
    <li><strong>Total Pembayaran:</strong> {{ isset($data['total_pembayaran']) ? 'Rp ' . number_format($data['total_pembayaran'], 0, ',', '.') : '-' }}</li>
</ul>
<p>Invoice pembayaran Anda terlampir dalam email ini.</p>
<p>Terima kasih atas kepercayaan Anda.</p>
