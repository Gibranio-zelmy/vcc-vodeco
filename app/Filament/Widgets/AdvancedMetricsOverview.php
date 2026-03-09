<?php

namespace App\Filament\Widgets;

use App\Models\Invoice;
use App\Models\Client;
use App\Models\Transaction;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Carbon\Carbon;

class AdvancedMetricsOverview extends BaseWidget
{
    protected static ?string $pollingInterval = '15s';
    protected static ?int $sort = 5; // Posisi terbawah untuk metrik advanced

    protected function getStats(): array
    {
        // 1. Radar AR Aging (Piutang Menggantung)
        // UPDATE MUTLAK: Hitung Unpaid, Partial, Overdue, dan kurangi dengan uang DP yang sudah masuk
        $arAging = Invoice::whereIn('status', ['Unpaid', 'Partial', 'Overdue'])
            ->get()
            ->sum(function ($invoice) {
                return $invoice->amount - $invoice->paid_amount;
            });

        // 2. Radar CAC (Customer Acquisition Cost) Bulan Ini
        $marketingSpend = Transaction::where('type', 'expense')
            ->where(function($q) {
                $q->where('category', 'ILIKE', '%Iklan%')
                  ->orWhere('category', 'ILIKE', '%Marketing%')
                  ->orWhere('category', 'ILIKE', '%Ads%');
            })
            ->whereMonth('transaction_date', Carbon::now()->month)
            ->whereYear('transaction_date', Carbon::now()->year)
            ->sum('amount') ?? 0;

        $newClientsThisMonth = Client::whereMonth('join_date', Carbon::now()->month)
            ->whereYear('join_date', Carbon::now()->year)
            ->count();
            
        $cac = $newClientsThisMonth > 0 ? $marketingSpend / $newClientsThisMonth : 0;

        // 3. Radar LTV (Lifetime Value)
        $totalIncome = Transaction::where('type', 'income')->sum('amount') ?? 0;
        $totalClients = Client::count();
        $ltv = $totalClients > 0 ? $totalIncome / $totalClients : 0;

        return [
            // Tambahan (float) untuk memaksa data menjadi angka desimal murni sebelum diformat
            Stat::make('AR Aging (Piutang)', 'Rp ' . number_format((float)$arAging, 0, ',', '.'))
                ->description('Uang Vodeco yang masih di tangan klien')
                ->color($arAging > 0 ? 'danger' : 'success')
                ->descriptionIcon('heroicon-m-exclamation-circle'),

            Stat::make('CAC (Biaya Akuisisi)', 'Rp ' . number_format((float)$cac, 0, ',', '.'))
                ->description('Modal marketing per 1 klien baru')
                ->color('warning')
                ->descriptionIcon('heroicon-m-megaphone'),

            Stat::make('Client LTV', 'Rp ' . number_format((float)$ltv, 0, ',', '.'))
                ->description('Rata-rata valuasi 1 klien')
                ->color('success')
                ->descriptionIcon('heroicon-m-star'),
        ];
    }
}