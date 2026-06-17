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
<!-- Tambahan Rekomendasi -->
         <!-- 🔥 REKOMENDASI PRODUK -->
        @if(isset($rekomendasi) && count($rekomendasi) > 0)
        <hr class="my-5">

        <h4 class="mb-4">Rekomendasi Untuk Anda</h4>
        <div class="card h-500">
          <div class="product-grid row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 row-cols-xl-5">
              @foreach($rekomendasi as $r)
              <div class="col">
                  <div class="product-item">

                      <a href="#" class="btn-wishlist"><svg width="24" height="24"><use xlink:href="#heart"></use></svg></a>
                      <figure>
                        <a href="{{ Storage::url($r->foto) }}" title="Product Title">
                          <img src="{{ Storage::url($r->foto) }}" class="img-fluid" style="width: 150px; height: 150px; object-fit: cover;">
                        </a>
                      </figure>
                      <h3>{{$r->nama_barang}}</h3>
                      <span class="qty"><b>Harga : {{rupiah($r->harga_barang*1.2)}}</b></span> <br>
                      <button class="btn btn-success btn-sm w-100"
                              onclick="tambahKeKeranjang({{ $r->id }})">
                              Tambah ke Keranjang
                      </button>

                  </div>
              </div>
              @endforeach
          </div>
        </div>
        @endif
        <!-- Akhir Tambahan Rekomendasi -->


      </div>
    </div>
  </div>
</section>

<!-- Tambahan script untuk payment gateway -->
<script type="text/javascript">
    // Pastikan Midtrans Snap.js sudah dimuat
    var payButton = document.getElementById('pay-button');
    payButton.addEventListener('click', function () {
        // console.log("Token:", "{{ $snap_token }}");
        window.snap.pay('{{$snap_token}}', {
        onSuccess: function(result){
            console.log('Pembayaran berhasil:', result);
            Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: 'Pembayaran Berhasil',
                    showConfirmButton: false,
                    timer: 2000 // Popup otomatis hilang setelah 2 detik
                });
            window.location.href = "/depan";
        },
        onPending: function(result){
            // console.log('Pembayaran tertunda:', result);
            Swal.fire({
                  icon: 'error',
                  title: 'Oops...',
                  text: 'Pembayaran Tertunda'
                });
            window.location.href = "/depan";
        },
        onError: function(result){
            // console.log('Pembayaran gagal:', result);
            Swal.fire({
                  icon: 'error',
                  title: 'Oops...',
                  text: 'Pembayaran Gagal'
                });
            // alert("Pembayaran gagal. Silakan coba lagi.");
            window.location.href = "/depan";
        },
        onClose: function(){
            alert("Anda menutup pop-up pembayaran sebelum menyelesaikan transaksi.");
        }
        });
    });
</script>

<!-- untuk sintak hapus data -->
 <script>
  function hapus(barang_id) {
        // console.log(barang_id);
        fetch('/hapus/'+barang_id, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // alert("Produk berhasil dihapus!");
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: 'Produk berhasil dihapus dari keranjang!',
                    showConfirmButton: false,
                    timer: 2000 // Popup otomatis hilang setelah 2 detik
                });

                // let vtotal = new Intl.NumberFormat("en-IN").format(data.total);
                let formatter = new Intl.NumberFormat('id-ID', {
                              style: 'currency',
                              currency: 'IDR',
                              minimumFractionDigits: 0
                            });
                let vtotal = formatter.format(data.total);
                document.getElementById('cart-total').textContent = "Total: " +vtotal;
                document.getElementById('total_belanja').textContent = vtotal;
                // jmlbarangdibeli
                document.getElementById('cart-count').textContent = data.jmlbarangdibeli;

                location.reload(); // Refresh tampilan
            } else {
                // alert("Gagal menghapus produk.");
                console.log(data);
                // Swal.fire({
                //   icon: 'error',
                //   title: 'Oops...',
                //   text: 'Gagal menghapus produk dari keranjang!'
                // });
            }
        })
        .catch(error => {
        console.error('Error:', error);
          Swal.fire({
              icon: 'error',
              title: 'Terjadi Kesalahan',
              text: error.message || 'Terjadi kesalahan saat menghapus produk.',
          });
        });
    }
 </script>
<script>
function tambahKeKeranjang(id) {
    // console.log("CLICK MASUK:", id); // 👈 DEBUG
    fetch('/tambah', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            product_id: id,
            quantity: 1
        })
    })
    .then(res => res.json())
    .then(data => {
        Swal.fire({
            icon: 'success',
            title: 'Ditambahkan!',
            text: 'Produk masuk ke keranjang',
            timer: 1500,
            showConfirmButton: false
        });

        location.reload();
    })
    .catch(err => console.log(err));
}
</script>
@endpush

@endsection
