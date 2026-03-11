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
            // INJEKSI MESIN PROFIL & 2FA MUTLAK
            // ==========================================
            ->plugins([
                BreezyCore::make()
                    ->myProfile(
                        shouldRegisterUserMenu: true, 
                        shouldRegisterNavigation: false, 
                        hasAvatars: true, // Aktifkan laci Foto Profil
                        slug: 'security-profile'
                    )
                    // TAMBAHAN MUTLAK: Mendeklarasikan komponen yang aktif
                    ->myProfileComponents([
                        'personal_info' => \Jeffgreco13\FilamentBreezy\Livewire\PersonalInfo::class,
                        'update_password' => \Jeffgreco13\FilamentBreezy\Livewire\UpdatePassword::class,
                        'two_factor' => \Jeffgreco13\FilamentBreezy\Livewire\TwoFactorAuthentication::class,
                    ])
                    ->enableTwoFactorAuthentication(
                        force: false,
                    )
            ])
            // (Blok userMenuItems dihapus mutlak karena diambil alih Breezy)
            ->colors([
                'primary' => \Filament\Support\Colors\Color::Emerald, // Hijau Terminal bawaan Bos
                'success' => \Filament\Support\Colors\Color::Emerald, // Hijau untuk Profit/Lunas
                'danger'  => \Filament\Support\Colors\Color::Rose,    // Merah untuk Piutang/Loss
                'warning' => \Filament\Support\Colors\Color::Amber,   // Oranye untuk DP/Pending
                'gray'    => \Filament\Support\Colors\Color::Zinc,    // Latar gelap solid
            ])
            ->font('JetBrains Mono') // Font presisi tinggi
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