@extends('layout')

@section('title', 'Keranjang Pelanggan')

@section('konten')

<div class="page-hero">
  <div class="container-fluid">
    <h1>🛒 Keranjang Anda</h1>
    <p>Kelola pesanan dan topping sebelum checkout.</p>
  </div>
</div>

<section class="pb-5">
  <div class="container-fluid">
    <div class="row g-4">

      <div class="col-lg-8">
        <h2 class="section-title">Item Pesanan</h2>

        @if(empty($keranjang))
          <div class="text-center py-5 text-muted">
            <p class="fs-5">Keranjang Anda masih kosong.</p>
            <a href="{{ route('pelanggan.dashboard') }}" class="btn btn-danger mt-2">Lihat Menu</a>
          </div>
        @else
          @foreach($keranjang as $itemKey => $item)
            <div class="card mb-3">
              <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                  <div>
                    <h5 class="card-title mb-1">{{ $item['nama'] }}</h5>
                    <p class="text-muted small mb-2">Harga menu: Rp {{ number_format($item['harga'], 0, ',', '.') }}</p>
                    <div class="mb-3">
                      <h6 class="mb-2">Rincian Pesanan</h6>
                      <ul class="list-unstyled small mb-0">
                        @if(!empty($item['rasa']))
                          <li><strong>Rasa:</strong> {{ $item['rasa'] }}</li>
                        @endif
                        @if(!empty($item['sayur_sawi']))
                          <li><strong>Sayur / Kuah:</strong> {{ $item['sayur_sawi'] }}</li>
                        @endif
                        @if(!empty($item['level_pedas']))
                          <li><strong>Level Pedas:</strong> {{ $item['level_pedas'] }}</li>
                        @endif
                        @if(!empty($item['catatan']))
                          <li><strong>Catatan:</strong> {{ $item['catatan'] }}</li>
                        @endif
                        @if(empty($item['rasa']) && empty($item['sayur_sawi']) && empty($item['level_pedas']) && empty($item['catatan']))
                          <li class="text-muted">Tidak ada rincian khusus</li>
                        @endif
                      </ul>
                    </div>
                    <p class="mb-2"><strong>Subtotal:</strong> Rp {{ number_format($item['subtotal'], 0, ',', '.') }}</p>
                  </div>
                  <div class="text-end">
                    <form action="{{ route('pelanggan.keranjang.remove') }}" method="POST">
                      @csrf
                      <input type="hidden" name="item_key" value="{{ $itemKey }}">
                      <button type="submit" class="btn btn-outline-danger btn-sm">Hapus</button>
                    </form>
                  </div>
                </div>

                <div class="d-flex align-items-center gap-2 mb-3">
                  <form action="{{ route('pelanggan.keranjang.update') }}" method="POST" class="d-inline">
                    @csrf
                    <input type="hidden" name="item_key" value="{{ $itemKey }}">
                    <input type="hidden" name="action" value="menu_decrease">
                    <button type="submit" class="btn btn-sm btn-outline-secondary">−</button>
                  </form>
                  <span class="fw-bold">{{ $item['qty'] }}</span>
                  <form action="{{ route('pelanggan.keranjang.update') }}" method="POST" class="d-inline">
                    @csrf
                    <input type="hidden" name="item_key" value="{{ $itemKey }}">
                    <input type="hidden" name="action" value="menu_increase">
                    <button type="submit" class="btn btn-sm btn-outline-secondary">+</button>
                  </form>
                  <span class="text-muted small">Menu</span>
                </div>

                @if(!empty($item['toppings']))
                  <div class="mb-3">
                    <h6 class="mb-2">Topping / Barang tambahan</h6>
                    @foreach($item['toppings'] as $topping)
                      <div class="d-flex justify-content-between align-items-center mb-2">
                        <div>
                          <div class="fw-semibold">{{ $topping['nama_barang'] }}</div>
                          <div class="text-muted small">Rp {{ number_format($topping['harga'], 0, ',', '.') }} x {{ $topping['qty'] }}</div>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                          <form action="{{ route('pelanggan.keranjang.update') }}" method="POST" class="d-inline">
                            @csrf
                            <input type="hidden" name="item_key" value="{{ $itemKey }}">
                            <input type="hidden" name="action" value="topping_decrease">
                            <input type="hidden" name="topping_id" value="{{ $topping['id_barang'] }}">
                            <button type="submit" class="btn btn-sm btn-outline-secondary">−</button>
                          </form>
                          <span class="fw-bold">{{ $topping['qty'] }}</span>
                          <form action="{{ route('pelanggan.keranjang.update') }}" method="POST" class="d-inline">
                            @csrf
                            <input type="hidden" name="item_key" value="{{ $itemKey }}">
                            <input type="hidden" name="action" value="topping_increase">
                            <input type="hidden" name="topping_id" value="{{ $topping['id_barang'] }}">
                            <button type="submit" class="btn btn-sm btn-outline-secondary">+</button>
                          </form>
                        </div>
                      </div>
                    @endforeach
                  </div>
                @endif

              </div>
            </div>
          @endforeach
        @endif
      </div>

      <div class="col-lg-4">
        <h2 class="section-title">Ringkasan</h2>
        <div class="card p-3 mb-3">
          <div class="d-flex justify-content-between mb-2">
            <span>Total pembayaran</span>
            <strong>Rp {{ number_format($total, 0, ',', '.') }}</strong>
          </div>
          <div class="text-muted small">Jumlah item: {{ collect($keranjang)->sum('qty') }}</div>
        </div>

        <div class="d-grid gap-2">
          <form action="{{ route('pelanggan.checkout.post') }}" method="POST">
            @csrf

            {{-- Pilihan Metode Pembayaran --}}
            <div class="mb-3">
              <label class="form-label fw-semibold mb-2">Metode Pembayaran</label>
              <div class="d-flex gap-2">

                {{-- QRIS --}}
                <input type="radio" class="btn-check" name="metode_pembayaran" id="metode_qris"
                       value="qris" required
                       {{ old('metode_pembayaran') === 'qris' ? 'checked' : '' }}>
                <label class="btn btn-outline-danger flex-fill text-center py-3" for="metode_qris"
                       style="border-radius:.75rem;">
                  <div style="font-size:1.6rem;">📱</div>
                  <div class="fw-bold" style="font-size:.85rem;">QRIS</div>
                  <div class="text-muted" style="font-size:.7rem;">Scan & Pay</div>
                </label>

                {{-- Tunai --}}
                <input type="radio" class="btn-check" name="metode_pembayaran" id="metode_tunai"
                       value="tunai"
                       {{ old('metode_pembayaran') === 'tunai' ? 'checked' : '' }}>
                <label class="btn btn-outline-success flex-fill text-center py-3" for="metode_tunai"
                       style="border-radius:.75rem;">
                  <div style="font-size:1.6rem;">💵</div>
                  <div class="fw-bold" style="font-size:.85rem;">Tunai</div>
                  <div class="text-muted" style="font-size:.7rem;">Bayar di kasir</div>
                </label>

              </div>
              @error('metode_pembayaran')
                <div class="text-danger small mt-1">{{ $message }}</div>
              @enderror
            </div>

            <button type="submit" class="btn btn-danger btn-lg w-100" {{ empty($keranjang) ? 'disabled' : '' }}>
              Lanjutkan Pembayaran
            </button>
          </form>
          <a href="{{ route('pelanggan.dashboard') }}" class="btn btn-outline-secondary">Tambah menu lain</a>
        </div>
      </div>

    </div>
  </div>
</section>

@endsection
