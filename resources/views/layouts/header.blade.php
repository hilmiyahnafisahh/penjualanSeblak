<!DOCTYPE html>
<html lang="en">
  <head>
    <title>FoodMart - Free eCommerce Grocery Store HTML Website Template</title>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="format-detection" content="telephone=no">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="author" content="">
    <meta name="keywords" content="">
    <meta name="description" content="">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-KK94CHFLLe+nY2dmCWGMq91rCGa5gtU4mk92HdvYe+M/SXH301p5ILy+dN9+nJOZ" crossorigin="anonymous">
    <link rel="stylesheet" type="text/css" href="{{asset('css/vendor.css')}}">
    <link rel="stylesheet" type="text/css" href="{{asset('style.css')}}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;700&family=Open+Sans:ital,wght@0,400;0,700;1,400;1,700&display=swap" rel="stylesheet">

    <!-- Tambahan Sweet Alert -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

     <!-- Tambahan untuk Midtrans -->
     <script type="text/javascript" src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ config('services.midtrans.client_key') }}"></script>

  </head>
  <body class="bg-light">
    <header class="navbar navbar-expand-lg navbar-light bg-white border-bottom mb-4">
      <div class="container-fluid">
        <a class="navbar-brand fw-bold" href="{{ route('depan') }}">Seblak Nusantara</a>
        <div class="d-flex align-items-center gap-2 ms-auto">
          @auth
            <a href="{{ route('depan') }}" class="btn btn-outline-secondary btn-sm">Menu</a>
            <a href="{{ route('cart.index') }}" class="btn btn-outline-secondary btn-sm">Keranjang</a>
            <a href="{{ route('history') }}" class="btn btn-outline-secondary btn-sm">Riwayat</a>
            <form method="POST" action="{{ route('logout') }}" class="d-inline">
              @csrf
              <button type="submit" class="btn btn-danger btn-sm">Logout</button>
            </form>
          @else
            <a href="{{ route('login') }}" class="btn btn-outline-secondary btn-sm">Masuk</a>
            <a href="{{ route('register') }}" class="btn btn-danger btn-sm">Daftar</a>
          @endauth
        </div>
      </div>
    </header>
    <main class="container-fluid">