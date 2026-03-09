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
    protected static ?int $sort = -1;

    // MANTRA MUTLAK: Paksa widget ini menjadi 4 kolom agar sejajar dan rapi menampung Total Kas
    protected function getColumns(): int
    {
        return 4;
    }

    protected function getStats(): array
    {
        // 1. Kalkulasi Total Kas Sepanjang Masa (Untuk hitung Runway & Tampilan Tangki Utama)
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
        $runwayText = $monthExpense > 0 ? $runway . ' Bulan' : 'Aman (>99 Bln)';

        // 4. Eksekusi Rumus Net Profit Margin (Persentase Keuntungan bersih)
        $monthProfit = $monthIncome - $monthExpense;
        $margin = $monthIncome > 0 ? round(($monthProfit / $monthIncome) * 100, 1) : 0;

        // 5. Eksekusi Rumus Revenue per Employee (Produktivitas per kepala)
        $activeEmployees = Employee::where('status', 'Active')->count();
        $revPerEmployee = $activeEmployees > 0 ? $monthIncome / $activeEmployees : 0;

        return [
            // KARTU BARU: TOTAL KAS BRANKAS ALL-TIME (Tangki Bensin Utama)
            Stat::make('Total Kas Brankas', 'Rp ' . number_format((float)$currentCash, 0, ',', '.'))
                ->description('Total uang murni Vodeco (All-Time)')
                ->color('success')
                ->descriptionIcon('heroicon-m-banknotes'),

            Stat::make('Company Runway', $runwayText)
                ->description('Batas napas vs pengeluaran bulan ini')
                ->color((float)$runway < 6 && $monthExpense > 0 ? 'danger' : 'success') 
                ->descriptionIcon('heroicon-m-clock'),
                
            Stat::make('Net Profit Margin', $margin . '%')
                ->description('Rasio profit riil bulan ini')
                ->color((float)$margin >= 20 ? 'success' : 'warning') 
                ->descriptionIcon('heroicon-m-chart-pie'),
                
            Stat::make('Rev. per Employee', 'Rp ' . number_format((float)$revPerEmployee, 0, ',', '.'))
                ->description('Produktivitas uang per anggota tim')
                ->color('info')
                ->descriptionIcon('heroicon-m-user'),
        ];
    }
}