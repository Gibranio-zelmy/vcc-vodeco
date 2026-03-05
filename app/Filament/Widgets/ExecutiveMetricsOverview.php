<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use App\Models\Employee;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Carbon\Carbon;

class ExecutiveMetricsOverview extends BaseWidget
{
    protected static ?string $pollingInterval = '15s';
    protected static ?int $sort = 4; // Berada di baris paling bawah

    protected function getStats(): array
    {
        // 1. Kalkulasi Total Kas Sepanjang Masa (Untuk hitung Runway)
        $totalIncome = Transaction::where('type', 'income')->sum('amount');
        $totalExpense = Transaction::where('type', 'expense')->sum('amount');
        $currentCash = $totalIncome - $totalExpense;

        // 2. Kalkulasi Data Bulan Ini
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;

        $monthIncome = Transaction::where('type', 'income')
            ->whereMonth('transaction_date', $currentMonth)
            ->whereYear('transaction_date', $currentYear)
            ->sum('amount');

        $monthExpense = Transaction::where('type', 'expense')
            ->whereMonth('transaction_date', $currentMonth)
            ->whereYear('transaction_date', $currentYear)
            ->sum('amount');

        // 3. Eksekusi Rumus Runway (Berapa bulan perusahaan bisa hidup tanpa pemasukan baru)
        $runway = $monthExpense > 0 ? round($currentCash / $monthExpense, 1) : 0;
        $runwayText = $runway > 0 ? $runway . ' Bulan' : 'Aman (Burn Rate 0)';

        // 4. Eksekusi Rumus Net Profit Margin (Persentase Keuntungan bersih)
        $monthProfit = $monthIncome - $monthExpense;
        $margin = $monthIncome > 0 ? round(($monthProfit / $monthIncome) * 100, 1) : 0;

        // 5. Eksekusi Rumus Revenue per Employee (Produktivitas per kepala)
        $activeEmployees = Employee::where('status', 'Active')->count();
        $revPerEmployee = $activeEmployees > 0 ? $monthIncome / $activeEmployees : 0;

        return [
            Stat::make('Company Runway', $runwayText)
                ->description('Batas hidup tanpa pemasukan baru')
                ->color($runway < 6 ? 'danger' : 'success') // Merah jika di bawah 6 bulan
                ->descriptionIcon('heroicon-m-clock'),
                
            Stat::make('Net Profit Margin', $margin . '%')
                ->description('Rasio profitabilitas riil bulan ini')
                ->color($margin >= 20 ? 'success' : 'warning') // Hijau jika profit > 20%
                ->descriptionIcon('heroicon-m-chart-pie'),
                
            Stat::make('Rev. per Employee', 'Rp ' . number_format($revPerEmployee, 0, ',', '.'))
                ->description('Produktivitas uang per anggota tim')
                ->color('info')
                ->descriptionIcon('heroicon-m-user'),
        ];
    }
}