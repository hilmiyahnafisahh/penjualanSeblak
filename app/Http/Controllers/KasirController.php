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
            ->orderBy('id', 'desc')
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

        $query = Pemesanan::with(['Pelanggan', 'pembayaran'])
            ->orderBy('id', 'desc');

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

    public function bayarPembayaran(Request $request, $id)
    {
        if (!$this->guardKasir($request)) {
            return redirect()->route('kasir.login');
        }

        $pembayaran = Pembayaran::with('pemesanan')->find($id);

        if (!$pembayaran) {
            abort(404);
        }

        if ($pembayaran->status_pembayaran !== 'pending' || strtolower($pembayaran->metode_pembayaran) !== 'tunai') {
            return redirect()->route('kasir.pembayaran')
                ->with('error', 'Hanya pembayaran tunai pending yang dapat dilunasi di kasir.');
        }

        $pembayaran->update([
            'status_pembayaran' => 'lunas',
            'tanggal_pembayaran' => $pembayaran->tanggal_pembayaran ?: now(),
        ]);

        if ($pembayaran->pemesanan) {
            $pembayaran->pemesanan->update(['status_pemesanan' => 'selesai']);
        }

        return redirect()->route('kasir.pembayaran')
            ->with('success', 'Pembayaran tunai berhasil diproses.');
    }

    public function selesaikanPesanan(Request $request, $id)
    {
        if (!$this->guardKasir($request)) {
            return redirect()->route('kasir.login');
        }

        $pemesanan = Pemesanan::with('pembayaran')->find($id);

        if (!$pemesanan) {
            abort(404);
        }

        // Hanya pesanan berstatus diproses dengan pembayaran QRIS lunas
        $pembayaran = $pemesanan->pembayaran;
        $metode     = strtolower($pembayaran?->metode_pembayaran ?? '');
        $status     = strtolower($pembayaran?->status_pembayaran ?? '');

        if ($pemesanan->status_pemesanan !== 'diproses'
            || $metode !== 'qris'
            || !in_array($status, ['lunas', 'settlement', 'capture'])) {
            return redirect()->route('kasir.pesanan', ['status' => 'diproses'])
                ->with('error', 'Pesanan tidak dapat diselesaikan.');
        }

        $pemesanan->update(['status_pemesanan' => 'selesai']);

        return redirect()->route('kasir.pesanan', ['status' => 'diproses'])
            ->with('success', 'Pesanan ' . $pemesanan->id_pesanan . ' berhasil diselesaikan.');
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