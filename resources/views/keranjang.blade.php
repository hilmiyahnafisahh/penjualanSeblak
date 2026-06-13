@extends('layout')

@section('title', 'Keranjang - Seblak Nusantara')

@section('konten')

{{-- Page Hero --}}
<div class="page-hero">
  <div class="container-fluid">
    <h1>🛒 Keranjang Anda</h1>
    <p>Periksa pesanan sebelum melanjutkan pembayaran</p>
  </div>
</div>

<section class="pb-5">
  <div class="container-fluid">
    <div class="row g-4">

      {{-- Daftar Item --}}
      <div class="col-lg-8">
        <h2 class="section-title">Item Pesanan</h2>

        @forelse($barang as $p)
        <div class="keranjang-item">
          <img src="{{ Storage::url($p->foto) }}" alt="{{ $p->nama_barang }}">
          <div class="flex-grow-1">
            <h6 class="mb-1 fw-bold">{{ $p->nama_barang }}</h6>
            <span class="text-muted small">{{ $p->total_barang }} Unit</span>
            <div class="fw-bold text-danger mt-1">{{ rupiah($p->total_belanja) }}</div>
          </div>
          <button class="btn btn-outline-danger btn-sm" onclick="hapus({{ $p->barang_id }})" title="Hapus">
            <svg width="18" height="18"><use xlink:href="#trash"></use></svg>
          </button>
        </div>
        @empty
        <div class="text-center py-5 text-muted">
          <p class="fs-5">Keranjang Anda masih kosong.</p>
          <a href="/depan" class="btn btn-danger mt-2">Lihat Menu</a>
        </div>
        @endforelse
      </div>

      {{-- Ringkasan --}}
      <div class="col-lg-4">
        <h2 class="section-title">Ringkasan</h2>
        <div class="keranjang-total-box">
          <div class="label">Total Pembayaran</div>
          <div class="amount">{{ rupiah($total_tagihan) }}</div>
        </div>
        @if(count($barang) > 0)
        <div class="d-grid mt-3">
          <button id="pay-button" class="btn btn-danger btn-lg fw-bold py-3">
            💳 Bayar Sekarang
          </button>
        </div>
        @endif
        <div class="d-grid mt-2">
          <a href="/depan" class="btn btn-outline-secondary">← Tambah Menu Lagi</a>
        </div>
      </div>

    </div>
  </div>
</section>

@push('scripts')
<script>
  // Tombol bayar via Midtrans
  var payButton = document.getElementById('pay-button');
  if (payButton) {
    payButton.addEventListener('click', function () {
      window.snap.pay('{{ $snap_token ?? "" }}', {
        onSuccess: function (result) {
          Swal.fire({ icon: 'success', title: 'Pembayaran Berhasil!', showConfirmButton: false, timer: 2000 });
          window.location.href = "/depan";
        },
        onPending: function (result) {
          Swal.fire({ icon: 'warning', title: 'Pembayaran Tertunda', text: 'Selesaikan pembayaran Anda.' });
          window.location.href = "/depan";
        },
        onError: function (result) {
          Swal.fire({ icon: 'error', title: 'Pembayaran Gagal', text: 'Silakan coba lagi.' });
          window.location.href = "/depan";
        },
        onClose: function () {
          Swal.fire({ icon: 'info', title: 'Dibatalkan', text: 'Anda menutup jendela pembayaran.' });
        }
      });
    });
  }

  // Hapus item
  function hapus(barang_id) {
    Swal.fire({
      title: 'Hapus item ini?',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#dc3545',
      cancelButtonText: 'Batal',
      confirmButtonText: 'Ya, hapus'
    }).then(result => {
      if (!result.isConfirmed) return;
      fetch('/hapus/' + barang_id, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') }
      })
      .then(async r => {
        if (!r.ok) {
          const contentType = r.headers.get('content-type') || '';
          const body = await r.text();
          if (contentType.includes('text/html') || body.includes('login')) {
            Swal.fire({
              icon: 'warning',
              title: 'Login diperlukan',
              text: 'Silakan login terlebih dahulu untuk menghapus item dari keranjang.',
              confirmButtonText: 'Login'
            }).then(() => window.location.href = '{{ route('login') }}');
            return;
          }
          throw new Error('Gagal menghapus item.');
        }
        return r.json();
      })
      .then(data => {
        if (!data) return;
        if (data.success) {
          Swal.fire({ icon: 'success', title: 'Dihapus!', showConfirmButton: false, timer: 1500 });
          syncCartUI(data.total, data.jmlbarangdibeli);
          location.reload();
        } else {
          Swal.fire({ icon: 'error', title: 'Gagal menghapus item.' });
        }
      })
      .catch(err => Swal.fire({ icon: 'error', title: 'Error', text: err.message }));
    });
  }
</script>
@endpush

@endsection
