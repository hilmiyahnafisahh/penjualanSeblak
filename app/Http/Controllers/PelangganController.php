<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Menu;
use App\Models\Barang;
use App\Models\Pemesanan;
use App\Models\Pembayaran;
use App\Models\Pelanggan;
use App\Models\Layanan;
use App\Models\DetailPesanan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;
use Midtrans\Config as MidtransConfig;
use Midtrans\Snap;

class PelangganController extends Controller
{
    protected function pelangganUser(Request $request)
    {
        $userId = $request->session()->get('pelanggan_user_id');

        return $userId ? User::find($userId) : null;
    }

    protected function guardPelanggan(Request $request)
    {
        $user = $this->pelangganUser($request);

        return $user && $user->user_group === 'pelanggan'
            ? $user
            : null;
    }

    public function index(Request $request)
    {
        return $this->guardPelanggan($request)
            ? redirect()->route('pelanggan.dashboard')
            : redirect()->route('pelanggan.login');
    }

    public function showLogin(Request $request)
    {
        if ($this->guardPelanggan($request)) {
            return redirect()->route('pelanggan.dashboard');
        }

        return view('pelanggan.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        $user = User::where('email', $credentials['email'])
            ->where('user_group', 'pelanggan')
            ->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return back()
                ->withErrors(['email' => 'Email atau password salah'])
                ->withInput();
        }

        $request->session()->put('pelanggan_user_id', $user->id);
        $request->session()->put('pelanggan_user_name', $user->name);

        return redirect()->route('pelanggan.dashboard');
    }

    public function register(Request $request)
    {
        if ($request->isMethod('get')) {
            if ($this->guardPelanggan($request)) {
                return redirect()->route('pelanggan.dashboard');
            }
            return view('pelanggan.register');
        }

        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'confirmed', 'min:8'],
        ]);

        $user = User::create([
            'name'       => $validated['name'],
            'email'      => $validated['email'],
            'password'   => Hash::make($validated['password']),
            'user_group' => 'pelanggan',
        ]);

        // Buat record pelanggan di tabel pelanggan agar data tersedia untuk proses checkout
        try {
            Pelanggan::create([
                'id_pelanggan'  => Pelanggan::getIDPelanggan(),
                'nama_pelanggan'=> $validated['name'],
                'jenis_kelamin' => 'Laki-laki',
                'alamat'        => '',
                'no_telp'       => '',
                'email'         => $validated['email'],
            ]);
        } catch (\Throwable $e) {
            // jika gagal membuat pelanggan, lanjutkan saja (user masih bisa login)
        }

        $request->session()->put('pelanggan_user_id', $user->id);
        $request->session()->put('pelanggan_user_name', $user->name);

        return redirect()->route('pelanggan.dashboard');
    }

    public function logout(Request $request)
    {
        $request->session()->forget([
            'pelanggan_user_id',
            'pelanggan_user_name',
        ]);

        return redirect()->route('pelanggan.login');
    }

    public function dashboard(Request $request)
    {
        if (!$this->guardPelanggan($request)) {
            return redirect()->route('pelanggan.login');
        }

        $user = $this->pelangganUser($request);

        $riwayatPesanan = Pemesanan::where('id_pelanggan', $user->id)
            ->orderBy('tanggal_pemesanan', 'desc')
            ->take(5)
            ->get();

        $kategoriParam = $request->query('kategori', 'Semua');
        $searchQuery   = $request->query('q', '');

        $kategoriList = Menu::select('kategori_menu')
            ->distinct()
            ->pluck('kategori_menu')
            ->toArray();

        $produkQuery = Menu::query();

        // Filter kategori
        if ($kategoriParam !== 'Semua' && in_array($kategoriParam, $kategoriList)) {
            $produkQuery->where('kategori_menu', $kategoriParam);
        }

        // Filter pencarian nama menu
        if (!empty($searchQuery)) {
            $produkQuery->where('nama_menu', 'like', '%' . $searchQuery . '%');
            $kategoriParam = 'Semua'; // reset kategori saat search
        }

        $produk = $produkQuery->orderBy('nama_menu')->get();
        $menu = Menu::orderBy('nama_menu')->get();
        $barang = Barang::orderBy('nama_barang')->get();
        $toppingBarang = Barang::where('stok', '>', 0)
            ->orderBy('nama_barang')
            ->get();
        $keranjang = Session::get('keranjang', []);

        return view('pelanggan.dashboard', compact(
            'user',
            'riwayatPesanan',
            'kategoriList',
            'kategoriParam',
            'searchQuery',
            'produk',
            'menu',
            'barang',
            'toppingBarang',
            'keranjang'
        ));
    }

    public function showMenu(Request $request, $id)
    {
        if (!$this->guardPelanggan($request)) {
            return redirect()->route('pelanggan.login');
        }

        $menu = Menu::where('id_menu', $id)->first();
        if (!$menu) {
            return redirect()->route('pelanggan.dashboard')->with('error', 'Menu tidak ditemukan.');
        }

        $toppingBarang = Barang::where('stok', '>', 0)
            ->orderBy('nama_barang')
            ->get();

        $rasaOptions = ['Gurih', 'Gurih Manis', 'Asin Manis'];
        $sayurOptions = ['Pakai Sayur Sawi', 'Tidak Pakai Sayur Sawi'];
        $levelPedasOptions = ['Level 0', 'Level 1', 'Level 2', 'Level 3'];

        return view('pelanggan.menu', compact(
            'menu',
            'toppingBarang',
            'rasaOptions',
            'sayurOptions',
            'levelPedasOptions'
        ));
    }

    public function addToCart(Request $request)
    {
        if (!$this->guardPelanggan($request)) {
            return redirect()->route('pelanggan.login');
        }

        $rasaOptions = ['Gurih', 'Gurih Manis', 'Asin Manis'];
        $sayurOptions = ['Pakai Sayur Sawi', 'Tidak Pakai Sayur Sawi'];
        $levelPedasOptions = ['Level 0', 'Level 1', 'Level 2', 'Level 3'];

        $data = $request->validate([
            'id_produk'   => ['required', 'string'],
            'qty'         => ['required', 'integer', 'min:1'],
            'rasa'        => ['nullable', 'string', Rule::in($rasaOptions)],
            'sayur_sawi'  => ['nullable', 'string', Rule::in($sayurOptions)],
            'level_pedas' => ['nullable', 'string', Rule::in($levelPedasOptions)],
            'catatan'     => ['nullable', 'string', 'max:200'],
            'toppings'               => ['sometimes', 'array'],
            'toppings.*.included'    => ['sometimes'],
            'toppings.*.qty'         => ['sometimes', 'integer', 'min:1'],
            'toppings.*.harga'       => ['sometimes', 'numeric', 'min:0'],
        ]);

        $menuItem = Menu::where('id_menu', $data['id_produk'])->first();
        if (!$menuItem) {
            return back()->with('error', 'Produk tidak ditemukan.');
        }

        $selectedToppings = [];
        foreach ($data['toppings'] ?? [] as $toppingId => $toppingData) {
            if (empty($toppingData['included'])) {
                continue;
            }

            $barangItem = Barang::where('id_barang', $toppingId)->first();
            if (!$barangItem) {
                continue;
            }

            $qtyTopping = max(1, (int) ($toppingData['qty'] ?? 1));
            $hargaTopping = $toppingData['harga'] ?? $barangItem->harga_jual;

            $selectedToppings[$toppingId] = [
                'id_barang' => $barangItem->id_barang,
                'nama_barang' => $barangItem->nama_barang,
                'qty' => $qtyTopping,
                'harga' => $hargaTopping,
                'subtotal' => $qtyTopping * $hargaTopping,
            ];
        }

        $selectedToppings = collect($selectedToppings)->sortBy('id_barang')->values()->all();

        $selectedOptions = [
            'rasa'        => $data['rasa'] ?? null,
            'sayur_sawi'  => $data['sayur_sawi'] ?? null,
            'level_pedas' => $data['level_pedas'] ?? null,
            'catatan'     => $data['catatan'] ?? null,
        ];

        $optionKey = md5(json_encode([
            'rasa' => $selectedOptions['rasa'],
            'sayur_sawi' => $selectedOptions['sayur_sawi'],
            'level_pedas' => $selectedOptions['level_pedas'],
            'catatan' => $selectedOptions['catatan'] ?? '',
            'toppings' => array_map(function ($topping) {
                return [
                    'id_barang' => $topping['id_barang'],
                    'qty' => $topping['qty'],
                    'harga' => $topping['harga'],
                ];
            }, $selectedToppings),
        ]));

        $cart = Session::get('keranjang', []);
        $itemKey = $menuItem->id_menu . '|' . $optionKey;

        if (isset($cart[$itemKey])) {
            $cart[$itemKey]['qty'] += $data['qty'];
            foreach ($selectedToppings as $toppingId => $topping) {
                if (isset($cart[$itemKey]['toppings'][$toppingId])) {
                    $cart[$itemKey]['toppings'][$toppingId]['qty'] += $topping['qty'];
                    $cart[$itemKey]['toppings'][$toppingId]['subtotal'] = $cart[$itemKey]['toppings'][$toppingId]['qty'] * $cart[$itemKey]['toppings'][$toppingId]['harga'];
                } else {
                    $cart[$itemKey]['toppings'][$toppingId] = $topping;
                }
            }
        } else {
            $cart[$itemKey] = [
                'id'          => $menuItem->id,
                'id_menu'     => $menuItem->id_menu,
                'nama'        => $menuItem->nama_menu,
                'qty'         => $data['qty'],
                'harga'       => $menuItem->harga_menu,
                'rasa'        => $selectedOptions['rasa'],
                'sayur_sawi'  => $selectedOptions['sayur_sawi'],
                'level_pedas' => $selectedOptions['level_pedas'],
                'catatan'     => $selectedOptions['catatan'],
                'toppings'    => $selectedToppings,
            ];
        }

        $cart[$itemKey]['topping_total'] = collect($cart[$itemKey]['toppings'] ?? [])->sum('subtotal');
        $cart[$itemKey]['subtotal'] = ($cart[$itemKey]['qty'] * $cart[$itemKey]['harga']) + $cart[$itemKey]['topping_total'];

        Session::put('keranjang', $cart);

        return back()->with('success', 'Produk berhasil ditambahkan ke keranjang.');
    }

    public function cart(Request $request)
    {
        if (!$this->guardPelanggan($request)) {
            return redirect()->route('pelanggan.login');
        }

        $keranjang = Session::get('keranjang', []);
        $total = collect($keranjang)->sum('subtotal');

        return view('pelanggan.keranjang', compact('keranjang', 'total'));
    }

    public function updateCart(Request $request)
    {
        if (!$this->guardPelanggan($request)) {
            return redirect()->route('pelanggan.login');
        }

        $request->validate([
            'item_key' => ['required', 'string'],
            'action' => ['required', 'string'],
            'topping_id' => ['sometimes', 'string'],
        ]);

        $cart = Session::get('keranjang', []);
        $itemKey = $request->input('item_key');
        $action = $request->input('action');

        if (!isset($cart[$itemKey])) {
            return back()->with('error', 'Item tidak ditemukan di keranjang.');
        }

        $item = $cart[$itemKey];

        if ($action === 'menu_decrease') {
            $item['qty'] = max(0, $item['qty'] - 1);
        } elseif ($action === 'menu_increase') {
            $item['qty'] += 1;
        } elseif (in_array($action, ['topping_decrease', 'topping_increase'])) {
            $toppingId = $request->input('topping_id');
            if (!$toppingId || empty($item['toppings'])) {
                return back()->with('error', 'Topping tidak ditemukan.');
            }

            foreach ($item['toppings'] as &$topping) {
                if ((string)$topping['id_barang'] === (string)$toppingId) {
                    if ($action === 'topping_decrease') {
                        $topping['qty'] = max(0, $topping['qty'] - 1);
                    } else {
                        $topping['qty'] += 1;
                    }
                    $topping['subtotal'] = $topping['qty'] * $topping['harga'];
                    break;
                }
            }
            unset($topping);
            $item['toppings'] = array_values(array_filter($item['toppings'], fn($top) => $top['qty'] > 0));
        }

        if ($item['qty'] <= 0) {
            unset($cart[$itemKey]);
        } else {
            $item['topping_total'] = collect($item['toppings'] ?? [])->sum('subtotal');
            $item['subtotal'] = ($item['qty'] * $item['harga']) + $item['topping_total'];
            $cart[$itemKey] = $item;
        }

        Session::put('keranjang', $cart);

        return back();
    }

    public function removeCartItem(Request $request)
    {
        if (!$this->guardPelanggan($request)) {
            return redirect()->route('pelanggan.login');
        }

        $request->validate([
            'item_key' => ['required', 'string'],
        ]);

        $cart = Session::get('keranjang', []);
        $itemKey = $request->input('item_key');

        if (isset($cart[$itemKey])) {
            unset($cart[$itemKey]);
            Session::put('keranjang', $cart);
        }

        return back();
    }

    public function checkout(Request $request)
    {
        if (!$this->guardPelanggan($request)) {
            return redirect()->route('pelanggan.login');
        }

        $user      = $this->pelangganUser($request);
        $keranjang = Session::get('keranjang', []);

        if (empty($keranjang)) {
            return redirect()->route('pelanggan.keranjang')
                ->with('error', 'Keranjang Anda masih kosong.');
        }

        $request->validate([
            'metode_pembayaran' => ['required', 'in:qris,tunai'],
        ], [
            'metode_pembayaran.required' => 'Pilih metode pembayaran terlebih dahulu.',
            'metode_pembayaran.in'       => 'Metode pembayaran tidak valid.',
        ]);

        $metodePembayaran = $request->input('metode_pembayaran');

        // Cari data pelanggan berdasarkan email, lalu fallback ke nama
        $pelanggan = Pelanggan::where('email', $user->email)->first();

        // Jika tidak ditemukan by email, coba by nama (case-insensitive)
        if (!$pelanggan) {
            $pelanggan = Pelanggan::whereRaw('LOWER(nama_pelanggan) = ?', [strtolower($user->name)])->first();
        }

        // Jika masih tidak ditemukan, buat otomatis dari data user
        if (!$pelanggan) {
            // Generate id_pelanggan berikutnya
            $lastId   = Pelanggan::orderBy('id', 'desc')->value('id_pelanggan') ?? 'PLG000';
            $num      = (int) substr($lastId, 3) + 1;
            $newKode  = 'PLG' . str_pad($num, 3, '0', STR_PAD_LEFT);

            $pelanggan = Pelanggan::create([
                'id_pelanggan'   => $newKode,
                'nama_pelanggan' => $user->name,
                'email'          => $user->email,
                'jenis_kelamin'  => '-',
                'alamat'         => '-',
                'no_telp'        => '-',
            ]);
        }

        $layanan = Layanan::first();
        if (!$layanan) {
            return redirect()->route('pelanggan.keranjang')
                ->with('error', 'Data layanan tidak tersedia. Hubungi admin.');
        }

        DB::beginTransaction();
        try {
            $pemesanan = Pemesanan::create([
                'id_pelanggan'      => $pelanggan->id,
                'id_layanan'        => $layanan->id,
                'id_pesanan'        => Pemesanan::getKodeFaktur(),
                'tanggal_pemesanan' => now(),
                'status_pemesanan'  => 'belumdibayar',
                'subtotal'          => 0,
            ]);

            $grandTotal = 0;

            foreach ($keranjang as $item) {
                $subtotalItem = (int) $item['harga'] * (int) $item['qty'];
                $toppingTotal = 0;
                $toppingData  = [];

                // Resolve id_menu: jika 'id' bukan integer valid, cari dari DB via 'id_menu' string
                $menuId = isset($item['id']) ? (int) $item['id'] : 0;
                if ($menuId <= 0 && isset($item['id_menu'])) {
                    $menuRecord = Menu::where('id_menu', $item['id_menu'])->first();
                    $menuId = $menuRecord ? (int) $menuRecord->id : 0;
                }
                if ($menuId <= 0) {
                    // Fallback: cari dari nama
                    $menuRecord = Menu::where('nama_menu', $item['nama'] ?? '')->first();
                    $menuId = $menuRecord ? (int) $menuRecord->id : 0;
                }
                if ($menuId <= 0) {
                    throw new \Exception('Menu tidak ditemukan untuk item: ' . ($item['nama'] ?? 'unknown'));
                }

                foreach ($item['toppings'] ?? [] as $topping) {
                    $toppingSubtotal = (int) $topping['qty'] * (float) $topping['harga'];
                    $toppingTotal   += $toppingSubtotal;
                    $toppingData[]   = [
                        'id_barang'   => $topping['id_barang'],
                        'nama_barang' => $topping['nama_barang'],
                        'qty'         => $topping['qty'],
                        'harga'       => $topping['harga'],
                        'subtotal'    => $toppingSubtotal,
                    ];
                }

                $subtotalTotal = $subtotalItem + $toppingTotal;
                $grandTotal   += $subtotalTotal;

                DetailPesanan::create([
                    'id_pemesanan' => $pemesanan->id,
                    'id_menu'      => $menuId,
                    'jumlah'       => $item['qty'],
                    'harga_menu'   => $item['harga'],
                    'harga_jual'   => $item['harga'],
                    'subtotal'     => $subtotalTotal,
                    'catatan'      => $item['catatan'] ?? null,
                    'topping'      => !empty($toppingData) ? $toppingData : null,
                ]);

                // ── Kurangi stok barang bahan menu (menubarang) ──
                $bahanMenu = \App\Models\menubarang::where('id_menu', $menuId)->get();
                foreach ($bahanMenu as $bahan) {
                    \App\Models\Barang::where('id', $bahan->id_barang)
                        ->where('stok', '>', 0)
                        ->decrement('stok', $item['qty']);
                }

                // ── Kurangi stok topping (barang langsung) ──
                foreach ($toppingData as $top) {
                    \App\Models\Barang::where('id_barang', $top['id_barang'])
                        ->where('stok', '>', 0)
                        ->decrement('stok', $top['qty']);
                }
            }

            DB::table('pemesanan')
                ->where('id', $pemesanan->id)
                ->update(['subtotal' => $grandTotal]);

            // Buat record pembayaran
            $pembayaran = Pembayaran::create([
                'id_pemesanan'       => $pemesanan->id,
                'metode_pembayaran'  => $metodePembayaran,
                'tanggal_pembayaran' => now(),
                'total_pembayaran'   => $grandTotal,
                'status_pembayaran'  => 'pending',
            ]);

            DB::commit();
            Session::forget('keranjang');

            // ── TUNAI: tampilkan halaman info kasir ──
            if ($metodePembayaran === 'tunai') {
                return view('pelanggan.checkout_tunai', compact('pemesanan'));
            }

            // ── QRIS: buat Snap Token Midtrans ──
            MidtransConfig::$serverKey    = config('services.midtrans.server_key');
            MidtransConfig::$isProduction = filter_var(config('services.midtrans.is_production', false), FILTER_VALIDATE_BOOLEAN);
            MidtransConfig::$isSanitized  = true;
            MidtransConfig::$is3ds        = true;

            // Bangun item_details dari keranjang yang sudah disimpan sebelumnya
            $itemDetails = [];
            foreach ($keranjang as $item) {
                // Resolve id integer sama seperti di atas
                $mId = isset($item['id']) ? (int) $item['id'] : 0;
                if ($mId <= 0 && isset($item['id_menu'])) {
                    $mr = Menu::where('id_menu', $item['id_menu'])->first();
                    $mId = $mr ? (int) $mr->id : 1;
                }
                $itemDetails[] = [
                    'id'       => (string) $mId,
                    'price'    => (int) $item['harga'],
                    'quantity' => (int) $item['qty'],
                    'name'     => mb_substr($item['nama'], 0, 50),
                ];
                // Tambahkan topping sebagai item terpisah
                foreach ($item['toppings'] ?? [] as $topping) {
                    $itemDetails[] = [
                        'id'       => 'TOP-' . $topping['id_barang'],
                        'price'    => (int) $topping['harga'],
                        'quantity' => (int) $topping['qty'],
                        'name'     => mb_substr('Topping: ' . $topping['nama_barang'], 0, 50),
                    ];
                }
            }

            $midtransOrderId = 'PLG-' . $pembayaran->id . '-' . time();

            $snapParams = [
                'transaction_details' => [
                    'order_id'     => $midtransOrderId,
                    'gross_amount' => (int) $grandTotal,
                ],
                'item_details'     => $itemDetails,
                'customer_details' => [
                    'first_name' => $pelanggan->nama_pelanggan ?? $user->name,
                    'email'      => $pelanggan->email,
                ],
            ];

            // Simpan order_id Midtrans ke pembayaran
            DB::table('pembayaran')
                ->where('id', $pembayaran->id)
                ->update(['midtrans_order_id' => $midtransOrderId]);

            $snapToken = Snap::getSnapToken($snapParams);

            return view('pelanggan.checkout_qris', compact('snapToken', 'pemesanan', 'pembayaran'));

        } catch (\Throwable $e) {
            DB::rollBack();
            // Tampilkan error detail saat development
            if (config('app.debug')) {
                throw $e;
            }
            return redirect()->route('pelanggan.keranjang')
                ->with('error', 'Gagal membuat pesanan: ' . $e->getMessage());
        }
    }

    public function checkoutQrisSuccess(Request $request)
    {
        if (!$this->guardPelanggan($request)) {
            return redirect()->route('pelanggan.login');
        }

        // Update status pembayaran jika order_id dikirim dari Midtrans callback JS
        $orderId = $request->query('order_id');
        if ($orderId) {
            $pembayaran = Pembayaran::where('midtrans_order_id', $orderId)->first();
            if ($pembayaran) {
                $pembayaran->update(['status_pembayaran' => 'lunas']);
                if ($pembayaran->pemesanan) {
                    $pembayaran->pemesanan->update(['status_pemesanan' => 'diproses']);
                }
            }
        }

        return redirect()->route('pelanggan.riwayat')
            ->with('success', 'Pembayaran berhasil! Pesanan sedang diproses.');
    }

    /**
     * Buat ulang Snap Token untuk pesanan QRIS yang belum dibayar
     * Dipanggil via AJAX dari halaman pesanan
     */
    public function bayarQris(Request $request, $id)
    {
        if (!$this->guardPelanggan($request)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $user      = $this->pelangganUser($request);
        $pelanggan = Pelanggan::where('email', $user->email)->first();

        $pemesanan = Pemesanan::with(['DetailPesanan.menu', 'pembayaran'])
            ->where('id_pesanan', $id)
            ->first();

        if (!$pemesanan || !$pelanggan) {
            return response()->json(['error' => 'Pesanan tidak ditemukan'], 404);
        }

        // Pastikan pesanan milik pelanggan ini
        if ($pemesanan->id_pelanggan !== $pelanggan->id) {
            return response()->json(['error' => 'Akses ditolak'], 403);
        }

        $pembayaran = $pemesanan->pembayaran;
        if (!$pembayaran || strtolower($pembayaran->metode_pembayaran) !== 'qris') {
            return response()->json(['error' => 'Bukan pesanan QRIS'], 400);
        }

        // Cek apakah sudah lunas
        $sudahBayar = in_array(strtolower($pembayaran->status_pembayaran), ['lunas', 'settlement', 'capture']);
        if ($sudahBayar) {
            return response()->json(['error' => 'Pesanan sudah lunas'], 400);
        }

        try {
            MidtransConfig::$serverKey    = config('services.midtrans.server_key');
            MidtransConfig::$isProduction = filter_var(config('services.midtrans.is_production', false), FILTER_VALIDATE_BOOLEAN);
            MidtransConfig::$isSanitized  = true;
            MidtransConfig::$is3ds        = true;

            $midtransOrderId = 'PLG-' . $pembayaran->id . '-' . time();

            // Build item details
            $itemDetails = [];
            foreach ($pemesanan->DetailPesanan as $detail) {
                $itemDetails[] = [
                    'id'       => (string) $detail->id_menu,
                    'price'    => (int) $detail->harga_menu,
                    'quantity' => (int) $detail->jumlah,
                    'name'     => mb_substr($detail->menu->nama_menu ?? 'Menu', 0, 50),
                ];
                foreach ($detail->topping ?? [] as $top) {
                    $itemDetails[] = [
                        'id'       => 'TOP-' . ($top['id_barang'] ?? 'x'),
                        'price'    => (int) ($top['harga'] ?? 0),
                        'quantity' => (int) ($top['qty'] ?? 1),
                        'name'     => mb_substr('Topping: ' . ($top['nama_barang'] ?? '-'), 0, 50),
                    ];
                }
            }

            $snapToken = Snap::getSnapToken([
                'transaction_details' => [
                    'order_id'     => $midtransOrderId,
                    'gross_amount' => (int) $pembayaran->total_pembayaran,
                ],
                'item_details'     => $itemDetails,
                'customer_details' => [
                    'first_name' => $pelanggan->nama_pelanggan ?? $user->name,
                    'email'      => $pelanggan->email,
                ],
            ]);

            // Update midtrans_order_id
            DB::table('pembayaran')->where('id', $pembayaran->id)
                ->update(['midtrans_order_id' => $midtransOrderId]);

            return response()->json([
                'snap_token'  => $snapToken,
                'order_id'    => $midtransOrderId,
                'total'       => $pembayaran->total_pembayaran,
                'id_pesanan'  => $pemesanan->id_pesanan,
            ]);

        } catch (\Throwable $e) {
            return response()->json(['error' => 'Gagal membuat token: ' . $e->getMessage()], 500);
        }
    }

    public function pesanan(Request $request)
    {
        if (!$this->guardPelanggan($request)) {
            return redirect()->route('pelanggan.login');
        }

        $user        = $this->pelangganUser($request);
        $statusParam = $request->query('status', 'belumdibayar');

        // Ambil id dari tabel pelanggan (bukan tabel users)
        $pelanggan = Pelanggan::where('email', $user->email)->first();
        $pelangganId = $pelanggan?->id ?? 0;

        $query = Pemesanan::where('id_pelanggan', $pelangganId)
            ->with(['DetailPesanan.menu', 'pembayaran'])
            ->orderBy('tanggal_pemesanan', 'desc');

        if ($statusParam !== 'semua') {
            $query->where('status_pemesanan', $statusParam);
        }

        $pesanan = $query->get();

        return view('pelanggan.pesanan', compact('pesanan', 'statusParam'));
    }

    public function riwayat(Request $request)
    {
        if (!$this->guardPelanggan($request)) {
            return redirect()->route('pelanggan.login');
        }

        $user        = $this->pelangganUser($request);
        $statusParam = $request->query('status', 'semua');

        $query = Pemesanan::where('id_pelanggan', function ($q) use ($user) {
                    $q->select('id')
                      ->from('pelanggan')
                      ->where('email', $user->email)
                      ->limit(1);
                })
                ->with(['DetailPesanan.menu', 'pembayaran'])
                ->orderBy('tanggal_pemesanan', 'desc');

        if ($statusParam !== 'semua') {
            $query->where('status_pemesanan', $statusParam);
        }

        $pesanan = $query->get();

        return view('pelanggan.riwayat', compact('pesanan', 'statusParam'));
    }
}