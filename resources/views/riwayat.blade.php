@extends('layout')

@section('title', 'Riwayat Pesanan - Seblak Nusantara')

@section('konten')

{{-- Page Hero --}}
<div class="page-hero">
  <div class="container-fluid">
    <h1>📋 Riwayat Pesanan</h1>
    <p>Lihat semua transaksi yang pernah Anda lakukan</p>
  </div>
</div>

<section class="pb-5">
  <div class="container-fluid">

    @php $totalTagihan = 0; @endphp

    @forelse($transaksi as $p)
      @php $totalTagihan += $p->tagihan; @endphp

      <div class="riwayat-card">
        <div class="card-header-custom d-flex justify-content-between align-items-center flex-wrap gap-2">
          <span>{{ $p->no_faktur }}</span>
          <span class="badge badge-status-{{ $p->status }} px-3 py-2 rounded-pill">
            {{ ucfirst($p->status) }}
          </span>
        </div>
        <div class="card-body-custom">
          <div class="row g-2 mb-2">
            <div class="col-sm-6 text-muted small">
              📅 {{ $p->tgl }}
            </div>
            <div class="col-sm-6 text-sm-end fw-bold text-danger">
              {{ rupiah($p->tagihan) }}
            </div>
          </div>

          @if(!empty($detail_barang[$p->id]))
          <ul class="list-unstyled mb-0 small text-muted">
            @foreach($detail_barang[$p->id] as $barang)
            <li class="d-flex justify-content-between border-bottom py-1">
              <span>{{ $barang->nama_barang }} × {{ $barang->jml }}</span>
              <span>{{ rupiah($barang->harga_jual * $barang->jml) }}</span>
            </li>
            @endforeach
          </ul>
          @endif
        </div>
      </div>

    @empty
      <div class="text-center py-5 text-muted">
        <p class="fs-5">Belum ada riwayat pesanan.</p>
        <a href="/depan" class="btn btn-danger mt-2">Pesan Sekarang</a>
      </div>
    @endforelse

    @if(count($transaksi) > 0)
    <div class="keranjang-total-box mt-4" style="max-width:400px; margin-left:auto;">
      <div class="label">Total Semua Transaksi</div>
      <div class="amount">{{ rupiah($totalTagihan) }}</div>
    </div>
    @endif

  </div>
</section>

@endsection
