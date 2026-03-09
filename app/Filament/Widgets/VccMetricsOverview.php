<?php

namespace App\Filament\Widgets;

use App\Models\Project;
use App\Models\Employee;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class VccMetricsOverview extends BaseWidget
{
    // Mempercepat refresh rate widget ala Bloomberg
    protected static ?string $pollingInterval = '15s'; 

    protected function getStats(): array
    {
        // 1. Sinkronisasi status baru: Proyek yang belum 'Completed'
        $activeProjects = Project::whereIn('status', ['Queue', 'In Progress', 'Review'])->count();
        
        // 2. Menghitung nilai proyek yang masih berstatus antrean awal (Queue)
        $pipelineValue = Project::where('status', 'Queue')->sum('project_value') ?? 0;

        return [
            Stat::make('Active Projects', $activeProjects . ' Proyek')
                ->description('Proyek Vodeco yang sedang berjalan')
                ->descriptionIcon('heroicon-m-briefcase')
                ->color('success'),
                
            Stat::make('Active Operators', Employee::where('status', 'Active')->count() . ' Orang')
                ->description('Tim Vodeco yang siap tempur')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('info'),
                
            // PROTEKSI MUTLAK: Format angka kebal error
            Stat::make('Pipeline Value (IDR)', 'Rp ' . number_format((float)$pipelineValue, 0, ',', '.'))
                ->description('Potensi uang di antrean (Queue)')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('warning'),
        ];
    }
}