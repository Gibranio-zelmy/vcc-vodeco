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
        // PROTEKSI MUTLAK: ?? 0 agar tidak error saat database baru di-reset
        $totalIncome = Transaction::where('type', 'income')->sum('amount') ?? 0;
        $totalExpense = Transaction::where('type', 'expense')->sum('amount') ?? 0;
        $currentCash = $totalIncome - $totalExpense;

        // 2. Kalkulasi Data Bulan Ini
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;

        $monthIncome = Transaction::where('type', 'income')
            ->whereMonth('transaction_date', $currentMonth)
            ->whereYear('transaction_date', $currentYear)
            ->sum('amount') ?? 0;

        $monthExpense = Transaction::where('type', 'expense')
            ->whereMonth('transaction_date', $currentMonth)
            ->whereYear('transaction_date', $currentYear)
            ->sum('amount') ?? 0;

        // 3. Eksekusi Rumus Runway (Berapa bulan perusahaan bisa hidup tanpa pemasukan baru)
        $runway = $monthExpense > 0 ? round($currentCash / $monthExpense, 1) : 0;
        $runwayText = $monthExpense > 0 ? $runway . ' Bulan' : 'Aman (Burn Rate 0)';

        // 4. Eksekusi Rumus Net Profit Margin (Persentase Keuntungan bersih)
        $monthProfit = $monthIncome - $monthExpense;
        $margin = $monthIncome > 0 ? round(($monthProfit / $monthIncome) * 100, 1) : 0;

        // 5. Eksekusi Rumus Revenue per Employee (Produktivitas per kepala)
        $activeEmployees = Employee::where('status', 'Active')->count();
        $revPerEmployee = $activeEmployees > 0 ? $monthIncome / $activeEmployees : 0;

        return [
            Stat::make('Company Runway', $runwayText)
                ->description('Batas hidup tanpa pemasukan baru')
                ->color((float)$runway < 6 && $monthExpense > 0 ? 'danger' : 'success') // Merah jika di bawah 6 bulan
                ->descriptionIcon('heroicon-m-clock'),
                
            Stat::make('Net Profit Margin', $margin . '%')
                ->description('Rasio profitabilitas riil bulan ini')
                ->color((float)$margin >= 20 ? 'success' : 'warning') // Hijau jika profit > 20%
                ->descriptionIcon('heroicon-m-chart-pie'),
                
            // SUNTIKAN (float) agar format miliaran tetap lurus dan rapi
            Stat::make('Rev. per Employee', 'Rp ' . number_format((float)$revPerEmployee, 0, ',', '.'))
                ->description('Produktivitas uang per anggota tim')
                ->color('info')
                ->descriptionIcon('heroicon-m-user'),
        ];
    }
}