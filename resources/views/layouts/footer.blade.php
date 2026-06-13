    <footer class="py-5 mt-5">
      <div class="container-fluid">
        <div class="row g-4">

          <div class="col-lg-3 col-md-6">
            <div class="footer-menu">
              <h5 class="widget-title">🌶️ Seblak Nusantara</h5>
              <p class="text-muted small">Nikmati cita rasa seblak autentik dengan berbagai pilihan topping dan level kepedasan.</p>
              <div class="social-links mt-3">
                <ul class="d-flex list-unstyled gap-2">
                  <li><a href="#" class="btn btn-outline-light btn-sm">FB</a></li>
                  <li><a href="#" class="btn btn-outline-light btn-sm">IG</a></li>
                  <li><a href="#" class="btn btn-outline-light btn-sm">YT</a></li>
                </ul>
              </div>
            </div>
          </div>

          <div class="col-md-2 col-sm-6">
            <div class="footer-menu">
              <h5 class="widget-title">Tentang Kami</h5>
              <ul class="menu-list list-unstyled">
                <li><a href="#" class="nav-link">Tentang Kami</a></li>
                <li><a href="#" class="nav-link">Karir</a></li>
                <li><a href="#" class="nav-link">Blog</a></li>
              </ul>
            </div>
          </div>

          <div class="col-md-2 col-sm-6">
            <div class="footer-menu">
              <h5 class="widget-title">Bantuan</h5>
              <ul class="menu-list list-unstyled">
                <li><a href="#" class="nav-link">FAQ</a></li>
                <li><a href="#" class="nav-link">Kontak</a></li>
                <li><a href="#" class="nav-link">Kebijakan Privasi</a></li>
                <li><a href="#" class="nav-link">Syarat & Ketentuan</a></li>
              </ul>
            </div>
          </div>

          <div class="col-md-2 col-sm-6">
            <div class="footer-menu">
              <h5 class="widget-title">Layanan</h5>
              <ul class="menu-list list-unstyled">
                <li><a href="/depan" class="nav-link">Menu</a></li>
                <li><a href="/lihatkeranjang" class="nav-link">Keranjang</a></li>
                <li><a href="/lihatriwayat" class="nav-link">Riwayat Pesanan</a></li>
              </ul>
            </div>
          </div>

          <div class="col-lg-3 col-md-6">
            <div class="footer-menu">
              <h5 class="widget-title">Newsletter</h5>
              <p class="small text-muted">Daftar untuk mendapatkan promo dan info terbaru.</p>
              <form class="d-flex mt-3 gap-0" role="newsletter">
                <input class="form-control rounded-start rounded-0 bg-light" type="email" placeholder="Alamat Email">
                <button class="btn btn-warning rounded-end rounded-0 fw-bold" type="submit">Daftar</button>
              </form>
            </div>
          </div>

        </div>
      </div>
    </footer>

    <div id="footer-bottom">
      <div class="container-fluid">
        <div class="row">
          <div class="col-md-6 copyright">
            <p>© 2025 Seblak Nusantara. Semua hak dilindungi.</p>
          </div>
          <div class="col-md-6 text-start text-md-end">
            <p>Dibuat dengan ❤️ untuk pecinta seblak</p>
          </div>
        </div>
      </div>
    </div>

    <script src="{{ asset('js/jquery-1.11.0.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ENjdO4Dr2bkBIFxQpeoTz1HIcje39Wm4jDKdf19U8gI4ddQ3GYNS7NTKfAdVQSZe" crossorigin="anonymous"></script>
    <script src="{{ asset('js/plugins.js') }}"></script>
    <script src="{{ asset('js/script.js') }}"></script>

    {{-- Sync cart badge antara offcanvas dan navbar --}}
    <script>
      function syncCartUI(total, count) {
        let formatter = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 });
        let formatted = formatter.format(total);
        document.getElementById('cart-total').textContent = formatted;
        document.getElementById('total_belanja').textContent = formatted;
        document.getElementById('cart-count').textContent = count;
        let navBadge = document.getElementById('cart-count-nav');
        if (navBadge) navBadge.textContent = count;
      }
    </script>

    </main>
    @stack('scripts')
  </body>
</html>
