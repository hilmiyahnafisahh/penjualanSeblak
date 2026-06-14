@extends('layout')

@section('title', 'Menu - Seblak Nusantara')

@section('konten')

{{-- Flash success --}}
@if(session('success'))
<script>
  document.addEventListener("DOMContentLoaded", function () {
    Swal.fire({ title: "Berhasil!", text: "{{ session('success') }}", icon: "success", timer: 3000, showConfirmButton: false });
  });
</script>
@endif

{{-- Page Hero --}}
<div class="page-hero">
  <div class="container-fluid">
    <h1>🍜 Menu Kami</h1>
    <p>Pilih menu favoritmu dan tambahkan ke keranjang</p>
  </div>
</div>

<section class="pb-5">
  <div class="container-fluid">
    <h2 class="section-title">Produk Tersedia</h2>

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 row-cols-xl-5 g-3">
      @forelse($barang as $p)
      <div class="col">
        <div class="product-item">
          <figure>
            <a href="{{ Storage::url($p->foto) }}" title="{{ $p->nama_barang }}">
              <img src="{{ Storage::url($p->foto) }}" alt="{{ $p->nama_barang }}">
            </a>
          </figure>
          <h3>{{ $p->nama_barang }}</h3>
          <span class="qty">Stok: {{ $p->stok }} Unit</span>
          <span class="rating">
            <svg width="16" height="16" class="text-warning"><use xlink:href="#star-solid"></use></svg>
            {{ $p->rating ?? '5.0' }}
          </span>
          <span class="price">{{ rupiah($p->harga_barang * 1.2) }}</span>

          <div class="d-flex align-items-center justify-content-between gap-2">
            <div class="input-group product-qty" style="max-width:130px;">
              <button type="button" class="btn btn-danger btn-sm btn-number quantity-left-minus"
                      data-id="{{ $p->id }}" data-type="minus">
                <svg width="14" height="14"><use xlink:href="#minus"></use></svg>
              </button>
              <input type="text" id="quantity-{{ $p->id }}" name="quantity"
                     class="form-control form-control-sm text-center input-number" value="1">
              <button type="button" class="btn btn-success btn-sm btn-number quantity-right-plus"
                      data-id="{{ $p->id }}" data-type="plus">
                <svg width="14" height="14"><use xlink:href="#plus"></use></svg>
              </button>
            </div>
            <button class="btn btn-danger btn-sm flex-grow-1 fw-bold"
                    onclick="addToCart({{ $p->id }})">
              + Keranjang
            </button>
          </div>
        </div>
      </div>
      @empty
      <div class="col-12 text-center py-5 text-muted">
        <p class="fs-5">Belum ada menu tersedia saat ini.</p>
      </div>
      @endforelse
    </div>
  </div>
</section>

@push('scripts')
<script>
  // Plus / Minus qty
  document.addEventListener("click", function (event) {
    let target = event.target.closest(".btn-number");
    if (!target) return;
    let productId = target.getAttribute("data-id");
    let input = document.getElementById("quantity-" + productId);
    if (!input) return;
    let val = parseInt(input.value) || 1;
    if (target.getAttribute("data-type") === "plus") {
      input.value = val + 1;
    } else if (val > 1) {
      input.value = val - 1;
    }
  });

  // Add to cart
  function addToCart(productId) {
    let input = document.getElementById("quantity-" + productId);
    let quantity = parseInt(input?.value) || 1;

    let formData = new FormData();
    formData.append('product_id', productId);
    formData.append('quantity', quantity);

    fetch('/tambah', {
      method: 'POST',
      headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') },
      body: formData
    })
    .then(async r => {
      if (!r.ok) {
        const contentType = r.headers.get('content-type') || '';
        const body = await r.text();
        if (contentType.includes('text/html') || body.includes('login')) {
          Swal.fire({
            icon: 'warning',
            title: 'Login diperlukan',
            text: 'Silakan login terlebih dahulu untuk menambahkan menu ke keranjang.',
            confirmButtonText: 'Login'
          }).then(() => window.location.href = '{{ route('login') }}');
          return;
        }
        throw new Error('Gagal menambahkan produk ke keranjang.');
      }
      return r.json();
    })
    .then(data => {
      if (!data) return;
      if (data.success) {
        Swal.fire({ icon: 'success', title: 'Ditambahkan!', text: 'Produk berhasil masuk keranjang.', showConfirmButton: false, timer: 1800 });
        syncCartUI(data.total, data.jmlbarangdibeli);
      } else {
        Swal.fire({ icon: 'error', title: 'Gagal', text: 'Gagal menambahkan produk ke keranjang.' });
      }
    })
    .catch(err => Swal.fire({ icon: 'error', title: 'Error', text: err.message }));
  }
</script>
@endpush

@endsection
