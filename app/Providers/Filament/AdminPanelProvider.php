<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
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
use Jeffgreco13\FilamentBreezy\BreezyCore;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->favicon(asset('favicon.png'))
            ->databaseNotifications()
            ->login()
            ->brandName('VCC TERMINAL') // Identitas Kokpit
            ->navigationGroups([
                'DATABASE',
                'CASHFLOW',
                'OPERATIONS',
                'ANALYTICS',
                'LEGAL & SOP',
                'SISTEM KEAMANAN',
            ])
            // ==========================================
            // INJEKSI MESIN PROFIL & 2FA MUTLAK (DUPLIKAT DIHANCURKAN)
            // ==========================================
            ->plugins([
                BreezyCore::make()
                    ->myProfile(
                        shouldRegisterUserMenu: true, 
                        shouldRegisterNavigation: false, 
                        hasAvatars: true, // Aktifkan laci Foto Profil
                        slug: 'security-profile'
                    )
                    // Blok myProfileComponents dihapus total dari sini
                    ->enableTwoFactorAuthentication(
                        force: false,
                    )
            ])
            // (Blok userMenuItems dihapus mutlak karena diambil alih Breezy)
            // Palet warna Vodeco: ungu gelap sebagai aksen utama
            ->colors([
                'primary' => Color::Violet,   // Aksen utama Vodeco (ungu)
                'success' => Color::Emerald,  // Tetap hijau untuk profit / sukses
                'danger'  => Color::Rose,     // Merah lembut untuk warning berat
                'warning' => Color::Amber,    // Kuning-oranye untuk DP / pending
                'gray'    => Color::Zinc,     // Abu gelap untuk background
            ])
            // Font fintech modern yang lebih bersih daripada monospace
            ->font('Inter')
            ->defaultThemeMode(\Filament\Enums\ThemeMode::Dark) // Dark Mode Abadi
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                \App\Filament\Pages\Dashboard::class, // Dashboard kustom Bos tetap aman
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                //Widgets\AccountWidget::class,
                //Widgets\FilamentInfoWidget::class,
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
            ]);
    }
}