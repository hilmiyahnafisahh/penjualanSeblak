@extends('layout')

@section('title', 'Daftar Pelanggan - Seblak Nusantara')

@section('konten')
<div class="page-hero">
    <div class="container-fluid">
        <h1>📝 Daftar Akun</h1>
        <p>Buat akun untuk memesan seblak dan melihat riwayat pesanan Anda.</p>
    </div>
</div>

<section class="pb-5">
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow-sm border-0">
                    <div class="card-body p-4">
                        <form method="POST" action="{{ route('register') }}">
                            @csrf

                            @if($errors->any())
                                <div class="alert alert-danger">
                                    <ul class="mb-0">
                                        @foreach($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <div class="mb-3">
                                <label for="name" class="form-label">Nama Lengkap</label>
                                <input id="name" type="text" name="name" value="{{ old('name') }}" class="form-control" required autofocus>
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input id="email" type="email" name="email" value="{{ old('email') }}" class="form-control" required>
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input id="password" type="password" name="password" class="form-control" required>
                            </div>

                            <div class="mb-3">
                                <label for="password_confirmation" class="form-label">Konfirmasi Password</label>
                                <input id="password_confirmation" type="password" name="password_confirmation" class="form-control" required>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-danger btn-lg">Daftar</button>
                            </div>
                        </form>

                        <div class="mt-4 text-center">
                            <p class="mb-0">Sudah punya akun? <a href="{{ route('login') }}">Masuk di sini</a>.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
