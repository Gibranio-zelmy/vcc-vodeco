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
            ->userMenuItems([
                'profile' => \Filament\Navigation\MenuItem::make()
                    ->label('Profil CEO')
                    ->url(fn (): string => '#') // Nanti kita buatkan halaman profil khusus jika Bos mau
                    ->icon('heroicon-m-user-circle'),
                'logout' => \Filament\Navigation\MenuItem::make()
                    ->label('Log Out Terminal')
                    ->icon('heroicon-m-arrow-right-on-rectangle')
                    ->color('danger'),
            ])
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