<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Filament\Pages\Widgets\BenchmarkChart; // Memanggil mesin grafik yang baru kita buat

class BenchmarkAnalytics extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-presentation-chart-line';
    protected static string $view = 'filament.pages.benchmark-analytics';
    protected static ?string $navigationGroup = 'ANALYTICS';
    protected static ?string $navigationLabel = 'Benchmark Kinerja';
    protected static ?string $title = 'Grafik Perbandingan Tutup Buku VIP';
    protected static ?int $navigationSort = 1;

    // SUNTIKAN MUTLAK: Memasang grafik di bagian atas halaman
    protected function getHeaderWidgets(): array
    {
        return [
            BenchmarkChart::class,
        ];
    }
}