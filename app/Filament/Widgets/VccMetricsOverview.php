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
        return [
            Stat::make('Active Projects', Project::where('status', 'Ongoing')->count())
                ->description('Proyek Vodeco yang sedang berjalan')
                ->descriptionIcon('heroicon-m-briefcase')
                ->color('success'),
                
            Stat::make('Active Operators', Employee::where('status', 'Active')->count())
                ->description('Tim Vodeco yang siap tempur')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('info'),
                
            Stat::make('Pipeline Value (IDR)', Project::where('status', 'Pipeline')->sum('project_value'))
                ->description('Potensi uang yang masih menggantung')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('warning'),
        ];
    }
}
