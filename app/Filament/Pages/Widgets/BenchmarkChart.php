<?php

namespace App\Filament\Pages\Widgets;

use App\Models\ReportArchive;
use Filament\Widgets\ChartWidget;

class BenchmarkChart extends ChartWidget
{
    protected static ?string $heading = 'Tren Kinerja: Omzet vs Pengeluaran vs Net Profit';
    protected int | string | array $columnSpan = 'full';
    protected static ?string $maxHeight = '400px';

    protected function getData(): array
    {
        // Menyedot 12 data Tutup Buku terakhir, diurutkan dari yang terlama ke terbaru
        $archives = ReportArchive::orderBy('created_at', 'asc')->take(12)->get();

        return [
            'datasets' => [
                [
                    'label' => 'Total Pemasukan (🟢)',
                    'data' => $archives->pluck('total_revenue')->toArray(), 
                    'borderColor' => '#10b981', // Emerald
                    'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                    'fill' => true,
                    'tension' => 0.4,
                ],
                [
                    'label' => 'Total Pengeluaran (🔴)',
                    'data' => $archives->pluck('total_expense')->toArray(),
                    'borderColor' => '#f43f5e', // Rose
                    'backgroundColor' => 'rgba(244, 63, 94, 0.1)',
                    'fill' => true,
                    'tension' => 0.4,
                ],
                [
                    'label' => 'Net Profit (🔵)',
                    'data' => $archives->pluck('net_profit')->toArray(),
                    'borderColor' => '#3b82f6', // Blue
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'fill' => true,
                    'tension' => 0.4,
                ],
            ],
            // Sumbu X: Menggabungkan kolom 'month' dan 'year' agar terbaca cantik
            'labels' => $archives->map(fn($item) => $item->month . ' ' . $item->year)->toArray(), 
        ];
    }

    protected function getType(): string
    {
        return 'line'; 
    }
}