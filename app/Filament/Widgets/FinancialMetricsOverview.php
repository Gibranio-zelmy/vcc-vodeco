<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Carbon\Carbon;

class FinancialMetricsOverview extends BaseWidget
{
    protected static ?string $pollingInterval = '15s'; 
    protected static ?int $sort = 2;

    protected function getStats(): array
    {
        // Radar dikunci mati untuk membaca bulan berjalan secara real-time
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;

        // PROTEKSI MUTLAK: Ditambah ?? 0 agar sistem selalu membaca angka murni
        $income = Transaction::where('type', 'income')
            ->whereMonth('transaction_date', $currentMonth)
            ->whereYear('transaction_date', $currentYear)
            ->sum('amount') ?? 0;

        $expense = Transaction::where('type', 'expense')
            ->whereMonth('transaction_date', $currentMonth)
            ->whereYear('transaction_date', $currentYear)
            ->sum('amount') ?? 0;

        $netCashflow = $income - $expense;

        return [
            // SUNTIKAN (float) agar angka miliaran formatnya sejajar rapi dan aman
            Stat::make('Pemasukan Bulan Ini', 'Rp ' . number_format((float)$income, 0, ',', '.'))
                ->color('success')
                ->description('Total uang masuk bulan ini')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->chart([7, 2, 10, 3, 15, 4, 17]), // Sparkline visual
                
            Stat::make('Pengeluaran (Burn Rate)', 'Rp ' . number_format((float)$expense, 0, ',', '.'))
                ->color('danger')
                ->description('Total uang keluar bulan ini')
                ->descriptionIcon('heroicon-m-fire')
                ->chart([3, 12, 4, 15, 2, 10, 5]),
                
            Stat::make('Net Cashflow', 'Rp ' . number_format((float)$netCashflow, 0, ',', '.'))
                ->description('Selisih Pemasukan & Pengeluaran')
                ->descriptionIcon($netCashflow >= 0 ? 'heroicon-m-check-circle' : 'heroicon-m-x-circle')
                ->color($netCashflow >= 0 ? 'success' : 'danger'),
        ];
    }
}