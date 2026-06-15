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
        $kategoriList = Menu::select('kategori_menu')
            ->distinct()
            ->pluck('kategori_menu')
            ->toArray();

        $produkQuery = Menu::query();
        if ($kategoriParam !== 'Semua' && in_array($kategoriParam, $kategoriList)) {
            $produkQuery->where('kategori_menu', $kategoriParam);
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
            'id_produk' => ['required', 'string'],
            'qty' => ['required', 'integer', 'min:1'],
            'rasa' => ['required', 'string', Rule::in($rasaOptions)],
            'sayur_sawi' => ['required', 'string', Rule::in($sayurOptions)],
            'level_pedas' => ['required', 'string', Rule::in($levelPedasOptions)],
            'catatan' => ['nullable', 'string', 'max:200'],
            'toppings' => ['sometimes', 'array'],
            'toppings.*.included' => ['sometimes'],
            'toppings.*.qty' => ['sometimes', 'integer', 'min:1'],
            'toppings.*.harga' => ['sometimes', 'numeric', 'min:0'],
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
            'rasa' => $data['rasa'],
            'sayur_sawi' => $data['sayur_sawi'],
            'level_pedas' => $data['level_pedas'],
            'catatan' => $data['catatan'] ?? null,
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
                'id' => $menuItem->id_menu,
                'nama' => $menuItem->nama_menu,
                'qty' => $data['qty'],
                'harga' => $menuItem->harga_menu,
                'toppings' => $selectedToppings,
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

        // Validasi metode pembayaran
        $request->validate([
            'metode_pembayaran' => ['required', 'in:qris,tunai'],
        ], [
            'metode_pembayaran.required' => 'Pilih metode pembayaran terlebih dahulu.',
            'metode_pembayaran.in'       => 'Metode pembayaran tidak valid.',
        ]);

        $metodePembayaran = $request->input('metode_pembayaran');

        // Cari data pelanggan dari tabel pelanggan berdasarkan email user
        $pelanggan = Pelanggan::where('email', $user->email)->first();
        if (!$pelanggan) {
            return redirect()->route('pelanggan.keranjang')
                ->with('error', 'Data pelanggan tidak ditemukan. Hubungi admin.');
        }

        // Ambil layanan pertama (default: Dine In / LYN001)
        $layanan = Layanan::first();
        if (!$layanan) {
            return redirect()->route('pelanggan.keranjang')
                ->with('error', 'Data layanan tidak tersedia. Hubungi admin.');
        }

        DB::beginTransaction();
        try {
            // Buat record pemesanan baru
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
                    'id_menu'      => $item['id'],
                    'jumlah'       => $item['qty'],
                    'harga_menu'   => $item['harga'],
                    'harga_jual'   => $item['harga'],
                    'subtotal'     => $subtotalTotal,
                    'catatan'      => $item['catatan'] ?? null,
                    'topping'      => !empty($toppingData) ? $toppingData : null,
                ]);
            }

            // Update subtotal pemesanan
            DB::table('pemesanan')
                ->where('id', $pemesanan->id)
                ->update(['subtotal' => $grandTotal]);

            // Buat record pembayaran dengan metode yang dipilih
            Pembayaran::create([
                'id_pemesanan'      => $pemesanan->id,
                'metode_pembayaran' => $metodePembayaran,
                'tanggal_pembayaran'=> now(),
                'total_pembayaran'  => $grandTotal,
                'status_pembayaran' => 'pending',
            ]);

            DB::commit();

            // Kosongkan keranjang
            Session::forget('keranjang');

            return redirect()->route('pelanggan.pesanan')
                ->with('success', 'Pesanan berhasil dibuat! No. Pesanan: ' . $pemesanan->id_pesanan . ' | Metode: ' . strtoupper($metodePembayaran));

        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->route('pelanggan.keranjang')
                ->with('error', 'Gagal membuat pesanan: ' . $e->getMessage());
        }
    }

    public function pesanan(Request $request)
    {
        if (!$this->guardPelanggan($request)) {
            return redirect()->route('pelanggan.login');
        }

        $user = $this->pelangganUser($request);
        $statusParam = $request->query('status', 'belumdibayar');

        $query = Pemesanan::where('id_pelanggan', $user->id)
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

        $user = $this->pelangganUser($request);
        $statusParam = $request->query('status', 'belum_bayar');

        $query = Pembayaran::whereHas('pemesanan', fn($q) => $q->where('id_pelanggan', $user->id))
            ->with('pemesanan')
            ->orderBy('tanggal_pembayaran', 'desc');

        if ($statusParam === 'belum_bayar') {
            $query->where('status_pembayaran', 'pending');
        } elseif ($statusParam === 'lunas') {
            $query->where('status_pembayaran', 'lunas');
        }

        $pembayaran = $query->get();

        return view('pelanggan.riwayat', compact('pembayaran', 'statusParam'));
    }
}