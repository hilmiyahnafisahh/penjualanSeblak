<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Filament\Support\Facades\FilamentView; // Wajib ditambahkan untuk memanggil Render Hook

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // ------------------------------------------------------------------
        // forceScheme hanya diaktifkan saat production / ngrok, tidak untuk local.
        // Kode ngrok bawaan kamu (hilangkan tanda // jika ingin diaktifkan kembali)
        // ------------------------------------------------------------------
        // if (config('app.env') === 'local') {
        //     \URL::forceScheme('https');
        // }

        // Menyuntikkan CSS Modifikasi Tema Mewah (Glassmorphism & Gradasi) tanpa NPM
        FilamentView::registerRenderHook(
            'panels::head.end',
            fn (): string => '
                <style>
                    /* 1. Sidebar Mewah dengan Gradasi Gelap */
                    aside.fi-sidebar {
                        background: linear-gradient(180deg, #3A1005 0%, #1A0702 100%) !important;
                        border-right: 1px solid rgba(255, 255, 255, 0.05) !important;
                    }

                    /* Warna teks menu belum aktif */
                    aside.fi-sidebar .fi-sidebar-item-label {
                        color: #FFEDD5 !important;
                    }

                    /* Warna icon menu belum aktif */
                    aside.fi-sidebar .fi-sidebar-item-icon {
                        color: #FDBA74 !important;
                    }

                    /* Efek menu aktif yang lebih modern (Gradient & Glow effect) */
                    aside.fi-sidebar .fi-sidebar-item-active {
                        background: linear-gradient(90deg, #EA580C 0%, #C2410C 100%) !important;
                        box-shadow: 0 4px 15px rgba(234, 88, 12, 0.4) !important;
                        border-radius: 0.75rem;
                    }
                    aside.fi-sidebar .fi-sidebar-item-active .fi-sidebar-item-label,
                    aside.fi-sidebar .fi-sidebar-item-active .fi-sidebar-item-icon {
                        color: #FFFFFF !important;
                    }

                    /* 2. Topbar Semi-Transparan (Glassmorphism) */
                    header.fi-topbar {
                        background: rgba(15, 23, 42, 0.7) !important;
                        backdrop-filter: blur(12px) !important;
                        -webkit-backdrop-filter: blur(12px) !important;
                        border-bottom: 1px solid rgba(255, 255, 255, 0.05) !important;
                    }

                    /* 3. Tabel & Card Mewah (Rounded & Floating Glass) */
                    .fi-ta-ctn, .fi-wi-stats-overview-stat, .fi-fo-component-ctn {
                        background: rgba(30, 41, 59, 0.6) !important; /* Warna slate semi-transparan */
                        backdrop-filter: blur(10px) !important;
                        -webkit-backdrop-filter: blur(10px) !important;
                        border-radius: 1.25rem !important; /* Sudut lebih membulat */
                        border: 1px solid rgba(255, 255, 255, 0.08) !important;
                        box-shadow: 0 10px 30px -5px rgba(0, 0, 0, 0.3) !important;
                        transition: all 0.3s ease;
                    }

                    /* Efek hover pada baris tabel agar lebih interaktif */
                    .fi-ta-row:hover {
                        background-color: rgba(255, 255, 255, 0.03) !important;
                    }
                </style>
            '
        );
    }
}