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

class InputPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('input')
            ->path('input')
            ->databaseNotifications() 
            ->login()
            ->brandName('VCC DATA ENTRY') // Nama loket pembeda
            ->navigationGroups([
                'PANDUAN & SOP',
                'FASE 1: KLIEN & PESANAN',
                'FASE 2: TAGIHAN & KASIR',
                'FASE 3: HRD & MANPOWER',
            ])
            ->colors([
                'primary' => Color::Blue, // Warna Biru Karyawan (Pembeda dari Hijau Bos)
            ])
            ->font('JetBrains Mono')
            ->defaultThemeMode(\Filament\Enums\ThemeMode::Light) // Terang, agar beda dengan kokpit Dark Mode Bos
            
            // PENTING: Mengarahkan folder resource ke folder khusus "Input" agar tidak bercampur dengan folder "Admin"
            ->discoverResources(in: app_path('Filament/Input/Resources'), for: 'App\\Filament\\Input\\Resources')
            ->discoverPages(in: app_path('Filament/Input/Pages'), for: 'App\\Filament\\Input\\Pages')
            
            // PENTING: Menghapus Dashboard bawaan agar mereka tidak melihat metrik apapun
            ->pages([
                // Kosong. Tidak ada halaman Dashboard.
            ])
            ->widgets([
                // Kosong. Tidak ada radar satupun.
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