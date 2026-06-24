<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class KasirSessionMiddleware
{
    /**
     * Auto-logout kasir jika session sudah expired atau tidak aktif.
     * Setiap request yang berhasil akan memperbarui timestamp aktivitas.
     */
    public function handle(Request $request, Closure $next)
    {
        $sessionKey    = 'kasir_user_id';
        $lastActiveKey = 'kasir_last_active';
        $timeoutMins   = (int) config('session.lifetime', 120);

        if ($request->session()->has($sessionKey)) {
            $lastActive = $request->session()->get($lastActiveKey);

            if ($lastActive && now()->diffInMinutes($lastActive) >= $timeoutMins) {
                // Session timeout — hapus semua data kasir dan redirect ke login
                $request->session()->forget([$sessionKey, 'kasir_user_name', $lastActiveKey]);

                if ($request->expectsJson()) {
                    return response()->json(['message' => 'Session expired'], 401);
                }

                return redirect()->route('kasir.login')
                    ->with('info', 'Sesi Anda telah berakhir. Silakan login kembali.');
            }

            // Perbarui timestamp aktivitas terakhir
            $request->session()->put($lastActiveKey, now());
        }

        return $next($request);
    }
}
