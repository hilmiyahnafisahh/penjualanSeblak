<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use App\Models\Pembayaran;
use App\Models\Pemesanan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class KasirController extends Controller
{
    protected function kasirUser(Request $request)
    {
        $userId = $request->session()->get('kasir_user_id');

        return $userId ? User::find($userId) : null;
    }

    protected function guardKasir(Request $request)
    {
        $user = $this->kasirUser($request);

        return $user && $user->user_group === 'kasir'
            ? $user
            : null;
    }

    public function index(Request $request)
    {
        return $this->guardKasir($request)
            ? redirect()->route('kasir.dashboard')
            : redirect()->route('kasir.login');
    }

    public function showLogin(Request $request)
    {
        if ($this->guardKasir($request)) {
            return redirect()->route('kasir.dashboard');
        }

        return view('kasir.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $user = User::where('email', $credentials['email'])
            ->where('user_group', 'kasir')
            ->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return back()
                ->withErrors([
                    'email' => 'Email atau password salah'
                ])
                ->withInput();
        }

        $request->session()->put('kasir_user_id', $user->id);
        $request->session()->put('kasir_user_name', $user->name);

        return redirect()->route('kasir.dashboard');
    }

    public function logout(Request $request)
    {
        $request->session()->forget([
            'kasir_user_id',
            'kasir_user_name'
        ]);

        return redirect()->route('kasir.login');
    }

    public function dashboard(Request $request)
    {
        if (!$this->guardKasir($request)) {
            return redirect()->route('kasir.login');
        }

        $pendingCount = Pemesanan::where('status_pemesanan', 'belumdibayar')->count();
        $diprosesCount = Pemesanan::where('status_pemesanan', 'diproses')->count();
        $belumBayarCount = Pembayaran::where('status_pembayaran', 'pending')->count();
        $todayRevenue = Pembayaran::whereDate('tanggal_pembayaran', now())->sum('total_pembayaran');

        $recentOrders = Pemesanan::with('Pelanggan')
            ->orderBy('tanggal_pemesanan', 'desc')
            ->take(5)
            ->get();

        return view('kasir.dashboard', compact(
            'pendingCount',
            'diprosesCount',
            'belumBayarCount',
            'todayRevenue',
            'recentOrders'
        ));
    }

    public function pesanan(Request $request)
    {
        if (!$this->guardKasir($request)) {
            return redirect()->route('kasir.login');
        }

        $statusParam = $request->query('status', 'pending');

        $statusMap = [
            'pending' => 'belumdibayar',
            'diproses' => 'diproses',
            'selesai' => 'selesai',
            'semua' => null,
        ];

        $query = Pemesanan::with('Pelanggan')
            ->orderBy('tanggal_pemesanan', 'desc');

        if ($statusMap[$statusParam] ?? null) {
            $query->where('status_pemesanan', $statusMap[$statusParam]);
        }

        $pesanan = $query->get();

        return view('kasir.pesanan', compact('pesanan', 'statusParam'));
    }

    public function pembayaran(Request $request)
    {
        if (!$this->guardKasir($request)) {
            return redirect()->route('kasir.login');
        }

        $statusParam = $request->query('status', 'belum_bayar');

        $query = Pembayaran::with('pemesanan.Pelanggan')
            ->orderBy('tanggal_pembayaran', 'desc');

        if ($statusParam === 'belum_bayar') {
            $query->where('status_pembayaran', 'pending');
        } elseif ($statusParam === 'lunas') {
            $query->where('status_pembayaran', 'lunas');
        }

        $pembayaran = $query->get();

        return view('kasir.pembayaran', compact('pembayaran', 'statusParam'));
    }

    public function stokMenu(Request $request)
    {
        if (!$this->guardKasir($request)) {
            return redirect()->route('kasir.login');
        }

        $tab = $request->query('tab', 'menu');

        $menuList = Menu::orderBy('nama_menu')->get();
        $barangList = \App\Models\Barang::orderBy('nama_barang')->get();

        return view('kasir.stok_menu', compact(
            'tab',
            'menuList',
            'barangList'
        ));
    }
}