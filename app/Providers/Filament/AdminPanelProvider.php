<?php

namespace App\Providers\Filament;

use Filament\Contracts\Plugin;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use App\Filament\Pages\Dashboard;
use Illuminate\Support\Facades\Blade;

use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Filament\Navigation\NavigationGroup;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Joaopaulolndev\FilamentWorldClock\FilamentWorldClockPlugin;
use App\Filament\Pages\Auth\Login;
use App\Filament\Widgets\DateTimeNow;
use App\Filament\Widgets\LaporanStatsOverview;
use App\Filament\Widgets\JenisLayananChart;
use App\Filament\Widgets\AntreanHarianChart;



class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login(Login::class) 

            ->brandLogo(fn () => view('filament.partials.brand-logo'))
            ->brandLogoHeight('2rem')

            ->favicon(asset('images/favicon.png'))

            ->colors([
                'primary' => Color::Blue,
                'danger' => Color::Rose,
                'gray' => Color::Slate,
                'info' => Color::Sky,
                'success' => Color::Emerald,
                'warning' => Color::Amber,
            ])
            
            // ✅ UI/UX Enhancements
            ->sidebarCollapsibleOnDesktop()  // Sidebar bisa di-collapse
            ->sidebarFullyCollapsibleOnDesktop() // Bisa full collapse
            ->breadcrumbs() // Breadcrumb navigation
            ->globalSearchKeyBindings(['command+k', 'ctrl+k']) // Global search shortcut
            ->spa() // SPA mode untuk navigasi lebih cepat
            
            // ✅ Navigation Groups dengan ikon
            ->navigationGroups([
                NavigationGroup::make()
                    ->label('Manajemen Antrean')
                    ->icon('heroicon-o-wrench-screwdriver')
                    ->collapsed(false),
                NavigationGroup::make()
                    ->label('Data Master')
                    ->icon('heroicon-o-circle-stack')
                    ->collapsed(true),
                NavigationGroup::make()
                    ->label('Manajemen Bengkel')
                    ->icon('heroicon-o-building-storefront')
                    ->collapsed(true),
                NavigationGroup::make()
                    ->label('Laporan')
                    ->icon('heroicon-o-document-chart-bar')
                    ->collapsed(true),
                NavigationGroup::make()
                    ->label('Pengaturan')
                    ->icon('heroicon-o-cog-6-tooth')
                    ->collapsed(true),
            ])
            
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Dashboard::class, 
            ])
            
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')

            
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
            
            ->plugins([
               
            ]);
    }

    
}