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

        $income = Transaction::where('type', 'income')
            ->whereMonth('transaction_date', $currentMonth)
            ->whereYear('transaction_date', $currentYear)
            ->sum('amount');

        $expense = Transaction::where('type', 'expense')
            ->whereMonth('transaction_date', $currentMonth)
            ->whereYear('transaction_date', $currentYear)
            ->sum('amount');

        $netCashflow = $income - $expense;

        return [
            Stat::make('Pemasukan Bulan Ini', 'Rp ' . number_format($income, 0, ',', '.'))
                ->color('success')
                ->chart([7, 2, 10, 3, 15, 4, 17]), 
                
            Stat::make('Pengeluaran (Burn Rate)', 'Rp ' . number_format($expense, 0, ',', '.'))
                ->color('danger')
                ->chart([3, 12, 4, 15, 2, 10, 5]),
                
            Stat::make('Net Cashflow', 'Rp ' . number_format($netCashflow, 0, ',', '.'))
                ->color($netCashflow >= 0 ? 'success' : 'danger'),
        ];
    }
}