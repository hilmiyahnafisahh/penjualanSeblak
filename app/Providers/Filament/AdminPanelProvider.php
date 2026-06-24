<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Filament\View\PanelsRenderHook;
use Illuminate\Support\HtmlString;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->brandName('Seblak Sangkuriang')
            ->brandLogo(new HtmlString('
                <div style="display:flex;align-items:center;gap:10px;">
                    <img src="' . asset('images/logo-seblak.png') . '" style="height:2.5rem;width:2.5rem;border-radius:50%;object-fit:contain;" alt="Logo">
                    <span style="color:#fff;font-weight:800;font-size:1rem;letter-spacing:.2px;line-height:1.2;">Seblak<br><span style="font-weight:400;font-size:.8rem;opacity:.85;">Sangkuriang</span></span>
                </div>
            '))
            ->brandLogoHeight('2.5rem')
            ->favicon(asset('images/logo-seblak.png'))
            ->font('Inter')
            ->darkMode(false)
            ->sidebarCollapsibleOnDesktop()
            ->sidebarFullyCollapsibleOnDesktop()
            ->colors([
                'primary' => Color::hex('#e85d04'),  // oranye
                'gray'    => Color::hex('#1a1a1a'),   // sidebar hitam
                'warning' => Color::Orange,
                'success' => Color::Emerald,
                'danger'  => Color::Rose,
                'info'    => Color::Blue,
            ])
            ->navigationGroups([
                NavigationGroup::make()->label('Transaksi'),
                NavigationGroup::make()->label('Master Data'),
                NavigationGroup::make()->label('Laporan'),
                NavigationGroup::make()->label('Analisis'),
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
                \App\Filament\Pages\LaporanPenjualan::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                \App\Filament\Widgets\DashboardStats::class,
                \App\Filament\Widgets\GrafikBeban::class,
                \App\Filament\Widgets\RateMenuTerlaris::class,
                \App\Filament\Widgets\GrafikPenjualan::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->renderHook(
                PanelsRenderHook::HEAD_END,
                fn (): HtmlString => new HtmlString('
                <style>
                    /* ── Sidebar gradient hitam-oranye-merah ── */
                    .fi-sidebar {
                        background: linear-gradient(160deg, #1a0a00 0%, #7c1d06 45%, #c0390b 80%, #e85d04 100%) !important;
                    }
                    .fi-sidebar-nav, .fi-sidebar-nav-groups {
                        background: transparent !important;
                    }
                    /* Override CSS variable Filament untuk active item */
                    :root {
                        --fi-sidebar-item-active-bg: rgba(255,220,80,.22);
                    }
                    /* Teks nav item */
                    .fi-sidebar-item-label,
                    .fi-sidebar-group-label {
                        color: rgba(255,255,255,.85) !important;
                    }
                    /* Nav item hover */
                    .fi-sidebar-item a:hover .fi-sidebar-item-label,
                    .fi-sidebar-item button:hover .fi-sidebar-item-label {
                        color: #fff !important;
                    }
                    .fi-sidebar-item a:hover,
                    .fi-sidebar-item button:hover {
                        background: rgba(255,255,255,.12) !important;
                    }
                    /* Icon warna putih */
                    .fi-sidebar-item-icon,
                    .fi-sidebar-item svg {
                        color: rgba(255,255,255,.8) !important;
                    }
                    /* Semua button sidebar transparan by default */
                    .fi-sidebar-item-button,
                    .fi-sidebar-item > a,
                    .fi-sidebar-item > button {
                        background: transparent !important;
                        box-shadow: none !important;
                    }
                    /* Group label */
                    .fi-sidebar-group-label {
                        color: rgba(255,200,150,.7) !important;
                        font-size: .7rem !important;
                        letter-spacing: .06em !important;
                    }
                    /* Brand area */
                    .fi-sidebar-header {
                        background: rgba(0,0,0,.25) !important;
                        border-bottom: 1px solid rgba(255,255,255,.1) !important;
                    }
                    .fi-brand-name {
                        color: #fff !important;
                        font-weight: 800 !important;
                    }
                    /* Active state — diset via JS di bawah */
                    .fi-sidebar-item-active-custom {
                        background: rgba(255,220,80,.25) !important;
                        box-shadow: inset 4px 0 0 #ffd060 !important;
                        border-radius: 8px !important;
                    }
                    .fi-sidebar-item-active-custom .fi-sidebar-item-label,
                    .fi-sidebar-item-active-custom span {
                        color: #ffd060 !important;
                        font-weight: 700 !important;
                    }
                    .fi-sidebar-item-active-custom svg {
                        color: #ffd060 !important;
                    }
                    /* CSS fallback - target aria-current yg Filament set */
                    .fi-sidebar [aria-current="page"],
                    .fi-sidebar [aria-current="true"],
                    .fi-sidebar a.fi-active,
                    .fi-sidebar button.fi-active,
                    .fi-sidebar .fi-sidebar-item-active {
                        background: rgba(255,220,80,.25) !important;
                        box-shadow: inset 4px 0 0 #ffd060 !important;
                        border-radius: 8px !important;
                    }
                    .fi-sidebar [aria-current="page"] span,
                    .fi-sidebar [aria-current="page"] .fi-sidebar-item-label,
                    .fi-sidebar [aria-current="true"] span,
                    .fi-sidebar a.fi-active span,
                    .fi-sidebar button.fi-active span,
                    .fi-sidebar .fi-sidebar-item-active span {
                        color: #ffd060 !important;
                        font-weight: 700 !important;
                    }
                    .fi-sidebar [aria-current="page"] svg,
                    .fi-sidebar [aria-current="true"] svg,
                    .fi-sidebar a.fi-active svg,
                    .fi-sidebar .fi-sidebar-item-active svg {
                        color: #ffd060 !important;
                    }
                    /* Scrollbar sidebar */
                    .fi-sidebar ::-webkit-scrollbar-track { background: transparent; }
                    .fi-sidebar ::-webkit-scrollbar-thumb { background: rgba(255,255,255,.2); border-radius: 4px; }
                    /* footer */
                    .fi-sidebar-footer {
                        border-top: 1px solid rgba(255,255,255,.1) !important;
                    }
                </style>
                <script>
                (function() {
                    function markActive() {
                        var current = window.location.pathname.replace(/\/$/, "");
                        var marked = false;

                        document.querySelectorAll(".fi-sidebar a[href], .fi-sidebar-item-button[href]").forEach(function(el) {
                            el.classList.remove("fi-sidebar-item-active-custom");
                            var href = (el.getAttribute("href") || "").replace(/\/$/, "");
                            if (!href || href === "#") return;

                            var isActive = false;
                            if (href === current) {
                                isActive = true;
                            } else if (href !== "/admin" && href.length > 6 && current.startsWith(href)) {
                                isActive = true;
                            }

                            if (isActive) {
                                el.classList.add("fi-sidebar-item-active-custom");
                                marked = true;
                            }
                        });
                        return marked;
                    }

                    // Jalankan berulang sampai sidebar ada
                    var tries = 0;
                    var interval = setInterval(function() {
                        if (markActive() || ++tries > 20) clearInterval(interval);
                    }, 150);

                    document.addEventListener("livewire:navigated", function() {
                        tries = 0;
                        var iv2 = setInterval(function() {
                            markActive();
                            if (++tries > 10) clearInterval(iv2);
                        }, 150);
                    });

                    // MutationObserver sebagai fallback
                    var observer = new MutationObserver(function() { markActive(); });
                    document.addEventListener("DOMContentLoaded", function() {
                        var sidebar = document.querySelector(".fi-sidebar");
                        if (sidebar) observer.observe(sidebar, { childList: true, subtree: true });
                    });
                })();
                </script>
                ')
            );
    }
}
