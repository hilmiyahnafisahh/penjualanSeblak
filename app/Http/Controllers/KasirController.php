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

        if (!$user) return null;

        // case-insensitive check untuk user_group
        if (strtolower($user->user_group) === 'kasir') return $user;

        // fallback: cek nama cocok dengan karyawan jabatan kasir
        $isKasir = \App\Models\Karyawan::whereRaw('LOWER(nama) = ?', [strtolower($user->name)])
            ->whereRaw('LOWER(jabatan) = ?', ['kasir'])
            ->exists();

        return $isKasir ? $user : null;
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
        $request->validate([
            'email'    => ['required', 'string'],
            'password' => ['required'],
        ]);

        $input = trim($request->input('email'));

        // Cari user berdasarkan email atau nama, tanpa filter user_group dulu
        $user = User::where(function ($q) use ($input) {
                $q->where('email', $input)
                  ->orWhereRaw('LOWER(name) = ?', [strtolower($input)]);
            })
            ->first();

        // Verifikasi password
        if (!$user || !Hash::check($request->input('password'), $user->password)) {
            return back()
                ->withErrors(['email' => 'Email/nama atau password salah'])
                ->withInput();
        }

        // Cek apakah user ini adalah karyawan dengan jabatan kasir
        // Cek di tabel karyawan berdasarkan nama (case-insensitive)
        $isKasir = strtolower($user->user_group) === 'kasir'
            || \App\Models\Karyawan::whereRaw('LOWER(nama) = ?', [strtolower($user->name)])
                ->whereRaw('LOWER(jabatan) = ?', ['kasir'])
                ->exists();

        if (!$isKasir) {
            return back()
                ->withErrors(['email' => 'Akun ini tidak memiliki akses kasir'])
                ->withInput();
        }

        // Update user_group jadi kasir kalau belum
        if ($user->user_group !== 'kasir') {
            $user->update(['user_group' => 'kasir']);
        }

        $request->session()->put('kasir_user_id', $user->id);
        $request->session()->put('kasir_user_name', $user->name);
        
        // Set Laravel Auth guard so Auth::user() works in views
        \Illuminate\Support\Facades\Auth::login($user);

        return redirect()->route('kasir.dashboard');
    }

    public function logout(Request $request)
    {
        $request->session()->forget([
            'kasir_user_id',
            'kasir_user_name'
        ]);
        
        // Clear Laravel Auth guard
        \Illuminate\Support\Facades\Auth::logout();

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