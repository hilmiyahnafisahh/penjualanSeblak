<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kirim Invoice Pembayaran</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
    <div class="container py-4">
        <h1 class="mb-4">Kirim Invoice Pembayaran</h1>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title">Detail Pembayaran</h5>
                <p><strong>No Pembayaran:</strong> {{ $pembayaran->id_pembayaran }}</p>
                <p><strong>No Pesanan:</strong> {{ $pembayaran->id_pemesanan }}</p>
                <p><strong>Nama Pelanggan:</strong> {{ $pembayaran->pemesanan?->Pelanggan?->nama_pelanggan ?? '-' }}</p>
                <p><strong>Email Pelanggan:</strong> {{ $pembayaran->pemesanan?->Pelanggan?->email ?? '-' }}</p>
                <p><strong>Total Pembayaran:</strong> Rp {{ number_format($pembayaran->total_pembayaran, 0, ',', '.') }}</p>
                <p><strong>Status:</strong> {{ ucfirst($pembayaran->status_pembayaran) }}</p>
            </div>
        </div>

        <form method="POST" action="{{ route('pembayaran.send_invoice', ['id' => $pembayaran->id]) }}">
            @csrf
            <div class="mb-3">
                <label for="email" class="form-label">Kirim ke alamat email</label>
                <input type="email" name="email" id="email" class="form-control" required value="{{ old('email', $pelangganEmail) }}">
            </div>
            <button type="submit" class="btn btn-primary">Kirim Invoice dengan PDF</button>
            <a href="{{ route('pembayaran.invoice', ['id' => $pembayaran->id]) }}" class="btn btn-secondary ms-2">Unduh PDF</a>
        </form>
    </div>
</body>
</html>
