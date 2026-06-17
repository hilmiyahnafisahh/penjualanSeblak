<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class PelangganSessionMiddleware
{
    /**
     * Auto-logout pelanggan jika session sudah expired atau tidak aktif.
     */
    public function handle(Request $request, Closure $next)
    {
        $sessionKey    = 'pelanggan_user_id';
        $lastActiveKey = 'pelanggan_last_active';
        $timeoutMins   = (int) config('session.lifetime', 120);

        if ($request->session()->has($sessionKey)) {
            $lastActive = $request->session()->get($lastActiveKey);

            if ($lastActive && now()->diffInMinutes($lastActive) >= $timeoutMins) {
                $request->session()->forget([$sessionKey, 'pelanggan_user_name', $lastActiveKey]);

                if ($request->expectsJson()) {
                    return response()->json(['message' => 'Session expired'], 401);
                }

                return redirect()->route('pelanggan.login')
                    ->with('info', 'Sesi Anda telah berakhir. Silakan login kembali.');
            }

            $request->session()->put($lastActiveKey, now());
        }

        return $next($request);
    }
}
