<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Pelanggan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (!Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors(['email' => 'Email atau password tidak valid.'])->withInput();
        }

        $request->session()->regenerate();

        return redirect()->intended(route('depan'));
    }

    public function showRegisterForm()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);
        
        // Jika pendaftaran adalah pelanggan, buat juga record di tabel pelanggan
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
            // jangan gagalkan registrasi user jika pembuatan pelanggan gagal
        }
        Auth::login($user);

        return redirect()->route('depan');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
