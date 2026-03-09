<?php

namespace App\Providers;
use Filament\Tables\Table;
use Illuminate\Support\ServiceProvider;

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
    // Menyuntikkan DNA Terminal ke semua tabel di VCC
    Table::configureUsing(function (Table $table): void {
        $table
            ->striped() // Memberikan warna belang/zebra di setiap baris agar mata presisi membaca angka dari kiri ke kanan
            ->defaultPaginationPageOption(50) // Langsung paksa tampilkan 50 baris data sekaligus (bawaan pabrik hanya 10)
            ->paginationPageOptions([50, 100, 250, 'all']); // Beri tombol ekstrem untuk menampilkan ratusan database di satu layar
    });
}
}
