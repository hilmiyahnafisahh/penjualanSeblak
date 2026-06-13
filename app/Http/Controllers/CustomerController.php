<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\Pelanggan;
use App\Models\Pemesanan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CustomerController extends Controller
{
    public function index()
    {
        $barang = Barang::all()->map(function ($item) {
            $item->foto = $item->gambar;
            return $item;
        });

        return view('galeri', compact('barang'));
    }

    public function cart()
    {
        $cart = session()->get('cart', []);

        $items = Barang::whereIn('id', array_keys($cart))->get()->map(function ($item) use ($cart) {
            $item->foto = $item->gambar;
            $item->barang_id = $item->id;
            $item->total_barang = $cart[$item->id] ?? 0;
            $item->total_belanja = $item->total_barang * $item->harga_barang;
            return $item;
        });

        $total_tagihan = $items->sum('total_belanja');

        return view('keranjang', [
            'barang' => $items,
            'total_tagihan' => $total_tagihan,
            'snap_token' => null,
        ]);
    }

    public function history()
    {
        $pelanggan = Pelanggan::where('email', Auth::user()->email)->first();

        if (!$pelanggan) {
            return view('riwayat', [
                'transaksi' => collect(),
                'detail_barang' => [],
            ]);
        }

        $transaksi = Pemesanan::with('DetailPesanan.menu')
            ->where('id_pelanggan', $pelanggan->id)
            ->get();

        $detail_barang = [];
        foreach ($transaksi as $item) {
            $detail_barang[$item->id] = $item->DetailPesanan;
        }

        return view('riwayat', compact('transaksi', 'detail_barang'));
    }

    public function addToCart(Request $request)
    {
        $data = $request->validate([
            'product_id' => ['required', 'integer', 'exists:barang,id'],
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        $cart = session()->get('cart', []);
        $cart[$data['product_id']] = ($cart[$data['product_id']] ?? 0) + $data['quantity'];
        session(['cart' => $cart]);

        $total = Barang::whereIn('id', array_keys($cart))->get()->sum(function ($item) use ($cart) {
            return ($cart[$item->id] ?? 0) * $item->harga_barang;
        });

        return response()->json([
            'success' => true,
            'total' => $total,
            'jmlbarangdibeli' => array_sum($cart),
        ]);
    }

    public function removeFromCart($barang_id)
    {
        $cart = session()->get('cart', []);

        if (isset($cart[$barang_id])) {
            unset($cart[$barang_id]);
            session(['cart' => $cart]);
        }

        $total = Barang::whereIn('id', array_keys($cart))->get()->sum(function ($item) use ($cart) {
            return ($cart[$item->id] ?? 0) * $item->harga_barang;
        });

        return response()->json([
            'success' => true,
            'total' => $total,
            'jmlbarangdibeli' => array_sum($cart),
        ]);
    }
}
