@extends('layout')

@section('title', 'Pesan ' . $menu->nama_menu)

@section('konten')
<div class="page-hero">
  <div class="container-fluid">
    <h1>Pesan {{ $menu->nama_menu }}</h1>
    <p>Pilih topping dan jumlah sebelum menambahkan ke keranjang.</p>
  </div>
</div>

<section class="pb-5">
  <div class="container-fluid">
    <div class="row g-4">
      <div class="col-lg-6">
        <div class="card p-4">
          <img src="{{ $menu->gambar_menu ? asset('storage/'.$menu->gambar_menu) : asset('images/placeholder-seblak.jpg') }}" class="img-fluid rounded mb-4" alt="{{ $menu->nama_menu }}">
          <h2>{{ $menu->nama_menu }}</h2>
          <p class="text-muted">Kategori: {{ $menu->kategori_menu }}</p>
          <p class="fs-5 text-danger">Rp {{ number_format($menu->harga_menu, 0, ',', '.') }}</p>
          <p>{{ $menu->deskripsi ?? 'Tambahkan topping favorit Anda.' }}</p>

          <form action="{{ route('pelanggan.keranjang.tambah') }}" method="POST">
            @csrf
            <input type="hidden" name="id_produk" value="{{ $menu->id_menu }}">
            <input type="hidden" name="qty" value="1" id="menuQty">

            <div class="mb-4">
              <label class="form-label">Jumlah pembelian</label>
              <div class="d-flex align-items-center gap-2">
                <button type="button" class="btn btn-outline-secondary" onclick="ubahQty(-1)">−</button>
                <span id="qtyValue" class="fw-bold">1</span>
                <button type="button" class="btn btn-outline-secondary" onclick="ubahQty(1)">+</button>
              </div>
            </div>

            <div class="mb-4">
              <h5>Rincian Pesanan</h5>
              <div class="mb-3">
                <label class="form-label">Rasa Seblak</label>
                <select name="rasa" class="form-select" required>
                  <option value="">Pilih rasa</option>
                  @foreach($rasaOptions as $rasa)
                    <option value="{{ $rasa }}">{{ $rasa }}</option>
                  @endforeach
                </select>
              </div>

              <div class="mb-3">
                <label class="form-label">Pilihan Sayur</label>
                <select name="sayur_sawi" class="form-select" required>
                  <option value="">Pilih sayur</option>
                  @foreach($sayurOptions as $sayur)
                    <option value="{{ $sayur }}">{{ $sayur }}</option>
                  @endforeach
                </select>
              </div>

              <div class="mb-3">
                <label class="form-label">Level Pedas</label>
                <select name="level_pedas" class="form-select" required>
                  <option value="">Pilih level pedas</option>
                  @foreach($levelPedasOptions as $level)
                    <option value="{{ $level }}">{{ $level }}</option>
                  @endforeach
                </select>
              </div>

              <div class="mb-3">
                <label class="form-label">Catatan tambahan (opsional)</label>
                <textarea name="catatan" class="form-control" rows="3" placeholder="Contoh: Kurangi garam, tidak pakai bawang"></textarea>
              </div>
            </div>

            <div class="mb-4">
              <h5>Pilih Topping / Barang Opsional</h5>
              @foreach($toppingBarang as $topping)
                <div class="form-check mb-3">
                  <input class="form-check-input" type="checkbox"
                         id="topping-{{ $topping->id_barang }}"
                         name="toppings[{{ $topping->id_barang }}][included]"
                         value="1">
                  <label class="form-check-label" for="topping-{{ $topping->id_barang }}">
                    {{ $topping->nama_barang }} — Rp {{ number_format($topping->harga_jual, 0, ',', '.') }}
                  </label>
                  <div class="mt-2 ms-4">
                    <label class="small text-muted">Jumlah</label>
                    <input type="number" name="toppings[{{ $topping->id_barang }}][qty]" value="1" min="1" class="form-control form-control-sm" style="width:100px;">
                    <input type="hidden" name="toppings[{{ $topping->id_barang }}][harga]" value="{{ $topping->harga_jual }}">
                  </div>
                </div>
              @endforeach
            </div>

            <button type="submit" class="btn btn-danger btn-lg w-100">Tambah Pesanan ke Keranjang</button>
          </form>
        </div>
      </div>
      <div class="col-lg-6">
        <div class="card p-4">
          <h5>Ringkasan Pesanan</h5>
          <p class="text-muted">Mohon pilih topping dan rincian pesanan, lalu tekan tombol tambah.</p>
          <ul class="list-group list-group-flush">
            <li class="list-group-item d-flex justify-content-between">
              <span>Menu</span>
              <strong>{{ $menu->nama_menu }}</strong>
            </li>
            <li class="list-group-item d-flex justify-content-between">
              <span>Rasa</span>
              <strong id="summaryRasa">-</strong>
            </li>
            <li class="list-group-item d-flex justify-content-between">
              <span>Sayur / Kuah</span>
              <strong id="summarySayur">-</strong>
            </li>
            <li class="list-group-item d-flex justify-content-between">
              <span>Level Pedas</span>
              <strong id="summaryPedas">-</strong>
            </li>
            <li class="list-group-item d-flex justify-content-between">
              <span>Harga dasar</span>
              <strong>Rp {{ number_format($menu->harga_menu, 0, ',', '.') }}</strong>
            </li>
            <li class="list-group-item d-flex justify-content-between">
              <span>Jumlah</span>
              <strong id="summaryQty">1</strong>
            </li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</section>

<script>
  function ubahQty(delta) {
    const qtyValue = document.getElementById('qtyValue');
    const inputQty = document.getElementById('menuQty');
    let qty = parseInt(qtyValue.textContent) + delta;
    if (qty < 1) qty = 1;
    qtyValue.textContent = qty;
    inputQty.value = qty;
    document.getElementById('summaryQty').textContent = qty;
  }

  function updateSummary() {
    const rasa = document.querySelector('[name="rasa"]').value || '-';
    const sayur = document.querySelector('[name="sayur_sawi"]').value || '-';
    const pedas = document.querySelector('[name="level_pedas"]').value || '-';

    document.getElementById('summaryRasa').textContent = rasa;
    document.getElementById('summarySayur').textContent = sayur;
    document.getElementById('summaryPedas').textContent = pedas;
  }

  document.addEventListener('DOMContentLoaded', function () {
    updateSummary();
    document.querySelector('[name="rasa"]').addEventListener('change', updateSummary);
    document.querySelector('[name="sayur_sawi"]').addEventListener('change', updateSummary);
    document.querySelector('[name="level_pedas"]').addEventListener('change', updateSummary);
  });
</script>
@endsection
