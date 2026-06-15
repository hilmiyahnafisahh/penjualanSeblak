<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class KasirController extends Controller
{
    public function index()
    {
        return redirect()->route('kasir.login');
    }

    public function showLogin()
    {
        return abort(404, 'Kasir login not implemented yet.');
    }

    public function login(Request $request)
    {
        return abort(404, 'Kasir login not implemented yet.');
    }

    public function logout(Request $request)
    {
        return abort(404, 'Kasir logout not implemented yet.');
    }

    public function dashboard()
    {
        return abort(404, 'Kasir dashboard not implemented yet.');
    }

    public function pesanan()
    {
        return abort(404, 'Kasir pesanan not implemented yet.');
    }

    public function pembayaran()
    {
        return abort(404, 'Kasir pembayaran not implemented yet.');
    }

    public function stokMenu()
    {
        return abort(404, 'Kasir stokMenu not implemented yet.');
    }
}
