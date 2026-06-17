<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesan {{ $menu->nama_menu }} | Seblak Sangkuriang</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/pelanggan.css') }}">
    <style>
        /* menu.blade.php specific */
        .menu-img    { width:100%; max-height:260px; object-fit:cover; border-radius:14px; }
        .topping-card { border:1.5px solid #e5e5e5; border-radius:.7rem; padding:.7rem; cursor:pointer; transition:all .15s; }
        .topping-card:has(input:checked) { border-color:var(--merah); background:#fff8f8; }
        .qty-btn { width:34px; height:34px; border-radius:50%; border:1.5px solid #ddd; background:white; font-size:1.2rem; display:flex; align-items:center; justify-content:center; cursor:pointer; transition:border-color .15s; }
        .qty-btn:hover { border-color:var(--merah); color:var(--merah); }
        .summary-row { display:flex; justify-content:space-between; align-items:center; padding:.45rem 0; border-bottom:1px solid #f5f5f5; font-size:.875rem; }
    </style>
</head>
<body>
<svg style="display:none;"><defs><symbol id="ic-user" viewBox="0 0 24 24"><path fill="currentColor" d="M12 12a5 5 0 1 0 0-10 5 5 0 0 0 0 10zm0 2c-5.33 0-8 2.67-8 4v1h16v-1c0-1.33-2.67-4-8-4z"/></symbol></defs></svg>

{{-- Navbar --}}
<nav class="navbar-custom">
  <div class="container-fluid px-3">
    <div class="d-flex align-items-center py-2 gap-3">
      <a href="{{ route('pelanggan.dashboard') }}" class="d-flex align-items-center gap-2 text-decoration-none">
        <img src="{{ asset('images/logo-seblak.png') }}" style="height:40px;width:40px;border-radius:50%;object-fit:contain;">
        <div class="d-none d-sm-block" style="font-weight:700;color:var(--merah);font-size:.95rem;">Seblak Sangkuriang</div>
      </a>
      <nav aria-label="breadcrumb" class="ms-2 d-none d-md-block">
        <ol class="breadcrumb mb-0 small">
          <li class="breadcrumb-item"><a href="{{ route('pelanggan.dashboard') }}" class="text-decoration-none" style="color:var(--merah);">Menu</a></li>
          <li class="breadcrumb-item active">{{ $menu->nama_menu }}</li>
        </ol>
      </nav>
      <div class="ms-auto"><a href="{{ route('pelanggan.keranjang') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-cart3 me-1"></i>Keranjang</a></div>
    </div>
  </div>
</nav>

<div class="container-fluid px-3 py-4" style="max-width:960px;">
  <div class="row g-4">
    {{-- Kiri: Info + Form --}}
    <div class="col-lg-7">
      @if($menu->gambar_menu)
        <img src="{{ asset('storage/'.$menu->gambar_menu) }}" class="menu-img mb-3" alt="{{ $menu->nama_menu }}">
      @else
        <div class="menu-img mb-3 d-flex align-items-center justify-content-center" style="background:#fdf0f0;font-size:5rem;">🍲</div>
      @endif
      <h3 class="fw-bold mb-1">{{ $menu->nama_menu }}</h3>
      <div class="text-muted small mb-1">{{ $menu->kategori_menu }}</div>
      <div class="fw-bold fs-5 mb-3" style="color:var(--merah);">Rp {{ number_format($menu->harga_menu, 0, ',', '.') }}</div>
      @if($menu->deskripsi)<p class="text-muted small mb-4">{{ $menu->deskripsi }}</p>@endif

      <form action="{{ route('pelanggan.keranjang.tambah') }}" method="POST" id="menuForm">
        @csrf
        <input type="hidden" name="id_produk" value="{{ $menu->id_menu }}">
        <input type="hidden" name="qty" value="1" id="hiddenQty">

        {{-- Jumlah --}}
        <div class="mb-4">
          <label class="fw-semibold mb-2 d-block">Jumlah</label>
          <div class="d-flex align-items-center gap-3">
            <button type="button" class="qty-btn" onclick="ubahQty(-1)">−</button>
            <span id="qtyDisplay" class="fw-bold fs-5">1</span>
            <button type="button" class="qty-btn" onclick="ubahQty(1)">+</button>
          </div>
        </div>

        {{-- Rasa --}}
        <div class="mb-3">
          <label class="fw-semibold mb-2 d-block">Rasa <span class="text-danger">*</span></label>
          <select name="rasa" class="form-select" required onchange="updateSummary()">
            <option value="">-- Pilih Rasa --</option>
            @foreach($rasaOptions as $r)<option value="{{ $r }}">{{ $r }}</option>@endforeach
          </select>
        </div>

        {{-- Sayur --}}
        <div class="mb-3">
          <label class="fw-semibold mb-2 d-block">Sayur / Kuah <span class="text-danger">*</span></label>
          <select name="sayur_sawi" class="form-select" required onchange="updateSummary()">
            <option value="">-- Pilih Sayur --</option>
            @foreach($sayurOptions as $s)<option value="{{ $s }}">{{ $s }}</option>@endforeach
          </select>
        </div>

        {{-- Catatan --}}
        <div class="mb-4">
          <label class="fw-semibold mb-2 d-block">Catatan (opsional)</label>
          <textarea name="catatan" class="form-control" rows="2" placeholder="Contoh: tidak pakai bawang, kurang asin..."></textarea>
        </div>

        {{-- Topping --}}
        @if($toppingBarang->isNotEmpty())
        <div class="mb-4">
          <label class="fw-semibold mb-2 d-block">Pilih Topping (opsional)</label>
          <div class="row g-2">
            @foreach($toppingBarang as $top)
            <div class="col-6">
              <div class="topping-card" onclick="toggleTopping('{{ $top->id_barang }}')">
                <div class="form-check d-flex justify-content-between align-items-center mb-1">
                  <label class="form-check-label fw-semibold" style="font-size:.85rem;cursor:pointer;">{{ $top->nama_barang }}</label>
                  <input class="form-check-input" type="checkbox" name="toppings[{{ $top->id_barang }}][included]" id="top_{{ $top->id_barang }}" value="1" onclick="event.stopPropagation()">
                </div>
                <div class="text-muted" style="font-size:.75rem;">Rp {{ number_format($top->harga_jual,0,',','.') }}</div>
                <input type="hidden" name="toppings[{{ $top->id_barang }}][harga]" value="{{ $top->harga_jual }}">
                <input type="number" name="toppings[{{ $top->id_barang }}][qty]" value="1" min="1" class="form-control form-control-sm mt-2" style="width:70px;" id="topQty_{{ $top->id_barang }}">
              </div>
            </div>
            @endforeach
          </div>
        </div>
        @endif

        <button type="submit" class="btn btn-merah w-100 py-3 fw-bold fs-6" id="btnTambahKeranjang">
          <i class="bi bi-cart-plus me-2"></i>Tambah ke Keranjang
        </button>
      </form>

      {{-- Popup Berhasil --}}
      <div class="modal fade" id="popupBerhasil" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
          <div class="modal-content border-0 rounded-4 shadow" style="overflow:hidden;">
            <div class="modal-body text-center p-4">
              <div style="font-size:3rem;margin-bottom:.5rem;">🛒</div>
              <h5 class="fw-bold mb-1" style="color:#0f5132;">Berhasil!</h5>
              <p class="text-muted small mb-3" id="popupMsg">{{ $menu->nama_menu }} berhasil ditambahkan ke keranjang.</p>
              <div class="d-grid gap-2">
                <a href="{{ route('pelanggan.keranjang') }}" class="btn btn-merah btn-sm py-2">
                  <i class="bi bi-cart3 me-1"></i>Lihat Keranjang
                </a>
                <button class="btn btn-outline-secondary btn-sm py-2" data-bs-dismiss="modal">
                  Lanjut Belanja
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    {{-- Kanan: Ringkasan --}}
    <div class="col-lg-5">
      <div class="summary-sticky bg-white rounded-3 p-4 border" style="border-color:#f0d0d0!important;">
        <h6 class="fw-bold mb-3">Ringkasan Pesanan</h6>
        <div class="summary-row"><span class="text-muted">Menu</span><span class="fw-semibold">{{ $menu->nama_menu }}</span></div>
        <div class="summary-row"><span class="text-muted">Rasa</span><span id="sumRasa" class="fw-semibold text-muted">Belum dipilih</span></div>
        <div class="summary-row"><span class="text-muted">Sayur</span><span id="sumSayur" class="fw-semibold text-muted">Belum dipilih</span></div>
        <div class="summary-row"><span class="text-muted">Jumlah</span><span id="sumQty" class="fw-semibold">1</span></div>
        <div class="summary-row"><span class="text-muted">Harga Dasar</span><span id="sumHargaDasar" class="fw-semibold">Rp {{ number_format($menu->harga_menu,0,',','.') }}</span></div>
        <div class="summary-row"><span class="text-muted">Topping</span><span id="sumTopping" class="fw-semibold">Rp 0</span></div>
        <hr>
        <div class="d-flex justify-content-between fw-bold"><span>Estimasi Total</span><span id="sumTotal" style="color:var(--merah);font-size:1.05rem;">Rp {{ number_format($menu->harga_menu,0,',','.') }}</span></div>
        <p class="text-muted mt-2" style="font-size:.75rem;">*Harga dapat berubah sesuai topping yang dipilih.</p>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
<script>
const hargaDasar = {{ $menu->harga_menu }};
let qty = 1;

function ubahQty(d) {
  qty = Math.max(1, qty + d);
  document.getElementById('qtyDisplay').textContent = qty;
  document.getElementById('hiddenQty').value = qty;
  document.getElementById('sumQty').textContent = qty;
  hitungTotal();
}

function toggleTopping(id) {
  const cb = document.getElementById('top_'+id);
  cb.checked = !cb.checked;
  hitungTotal();
}

function updateSummary() {
  const rasa  = document.querySelector('[name="rasa"]').value;
  const sayur = document.querySelector('[name="sayur_sawi"]').value;
  document.getElementById('sumRasa').textContent  = rasa  || 'Belum dipilih';
  document.getElementById('sumSayur').textContent = sayur || 'Belum dipilih';
  document.getElementById('sumRasa').style.color  = rasa  ? '#1a1a1a':'#aaa';
  document.getElementById('sumSayur').style.color = sayur ? '#1a1a1a':'#aaa';
  hitungTotal();
}

function hitungTotal() {
  let toppingTotal = 0;
  document.querySelectorAll('input[type="checkbox"][name$="[included]"]:checked').forEach(cb => {
    const id = cb.id.replace('top_','');
    const harga = parseInt(document.querySelector('input[name="toppings['+id+'][harga]"]').value) || 0;
    const qtyT  = parseInt(document.getElementById('topQty_'+id)?.value || 1);
    toppingTotal += harga * qtyT;
  });
  const total = (hargaDasar * qty) + toppingTotal;
  document.getElementById('sumHargaDasar').textContent = 'Rp ' + (hargaDasar * qty).toLocaleString('id-ID');
  document.getElementById('sumTopping').textContent = 'Rp ' + toppingTotal.toLocaleString('id-ID');
  document.getElementById('sumTotal').textContent = 'Rp ' + total.toLocaleString('id-ID');
}

document.querySelectorAll('input[type="checkbox"]').forEach(cb => cb.addEventListener('change', hitungTotal));
document.querySelectorAll('input[type="number"]').forEach(n => n.addEventListener('input', hitungTotal));

// Submit form via AJAX → tampilkan popup
document.getElementById('menuForm').addEventListener('submit', function(e) {
  e.preventDefault();
  const btn   = document.getElementById('btnTambahKeranjang');
  const form  = this;
  const nama  = '{{ $menu->nama_menu }}';

  btn.disabled = true;
  btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Menambahkan...';

  fetch(form.action, {
    method: 'POST',
    headers: {
      'X-CSRF-TOKEN': '{{ csrf_token() }}',
      'Accept': 'application/json, text/html, */*',
    },
    body: new FormData(form),
    redirect: 'follow',
  })
  .then(response => {
    // Sukses jika status 200-399 atau redirect
    if (response.ok || response.redirected) {
      document.getElementById('popupMsg').textContent = nama + ' berhasil ditambahkan ke keranjang.';
      const modal = new bootstrap.Modal(document.getElementById('popupBerhasil'));
      modal.show();
      btn.disabled = false;
      btn.innerHTML = '<i class="bi bi-cart-plus me-2"></i>Tambah ke Keranjang';
    } else {
      // Fallback: submit biasa
      form.submit();
    }
  })
  .catch(() => {
    // Fallback: submit biasa jika fetch error
    form.submit();
  });
});
</script>
</body>
</html>
