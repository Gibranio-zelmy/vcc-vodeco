<?php

namespace App\Providers;

use Filament\Tables\Table;
use Illuminate\Support\ServiceProvider;
use Filament\Http\Responses\Auth\Contracts\LoginResponse;
use App\Http\Responses\LoginResponse as CerdasLoginResponse;
use Livewire\Livewire; // Tambahkan pemanggil Livewire di atas agar lebih rapi

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // 3. TAMBAHKAN KODE INI DI DALAM SINI
        $this->app->singleton(
            LoginResponse::class,
            CerdasLoginResponse::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Menyuntikkan DNA Terminal ke semua tabel di VCC
        Table::configureUsing(function (Table $table): void {
            $table
                ->striped() // Memberikan warna belang/zebra di setiap baris
                ->defaultPaginationPageOption(50) // Langsung paksa tampilkan 50 baris
                ->paginationPageOptions([50, 100, 250, 'all']); // Tombol ekstrem
        });

        // ==========================================
        // SUNTIKAN PAKSA OTAK LIVEWIRE BREEZY (PROFIL & 2FA)
        // ==========================================
        Livewire::component('personal_info', \Jeffgreco13\FilamentBreezy\Livewire\PersonalInfo::class);
        Livewire::component('update_password', \Jeffgreco13\FilamentBreezy\Livewire\UpdatePassword::class);
        Livewire::component('two_factor_authentication', \Jeffgreco13\FilamentBreezy\Livewire\TwoFactorAuthentication::class);
    }
}