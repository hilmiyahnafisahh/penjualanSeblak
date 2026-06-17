<?php

namespace App\Providers\Filament;

use Filament\Panel;
use Filament\Pages;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Navigation\NavigationGroup;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;

use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Filament\View\PanelsRenderHook;
use Illuminate\Support\Facades\Blade;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->viteTheme('resources/css/filament/admin/theme.css')

            ->brandName('Seblak Sangkuriang')
            ->brandLogo(asset('images/logo.png'))
            ->brandLogoHeight('3rem')
            ->favicon(asset('images/logo.png'))
            ->font('Inter')
            ->darkMode(false)
            ->sidebarCollapsibleOnDesktop()
            ->sidebarFullyCollapsibleOnDesktop()

            ->colors([
                'primary'   => Color::hex('#DC2626'),
                'gray'      => Color::Stone,
                'warning'   => Color::Orange,
                'success'   => Color::Emerald,
                'danger'    => Color::Rose,
                'info'      => Color::Blue,
            ])

            ->navigationGroups([
                NavigationGroup::make()->label('TRANSAKSI'),
                NavigationGroup::make()->label('MASTER DATA'),
                NavigationGroup::make()->label('LAPORAN'),
            ])

            ->discoverResources(
                in: app_path('Filament/Resources'),
                for: 'App\\Filament\\Resources'
            )

            ->discoverPages(
                in: app_path('Filament/Pages'),
                for: 'App\\Filament\\Pages'
            )

            ->pages([
                Pages\Dashboard::class,
                \App\Filament\Pages\LaporanPenjualan::class,
            ])

            ->discoverWidgets(
                in: app_path('Filament/Widgets'),
                for: 'App\\Filament\\Widgets'
            )

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
                PanelsRenderHook::BODY_END,
                fn (): string => Blade::render('@livewire(\'chat-asisten\')'),
            );
    }
}