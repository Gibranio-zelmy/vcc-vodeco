<?php

namespace App\Filament\Widgets;

use App\Models\Employee;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class TeamUtilizationOverview extends BaseWidget
{
    protected static ?string $pollingInterval = '15s';
    protected static ?int $sort = 3; // Menjaga urutan di bawah radar finansial

    protected function getStats(): array
    {
        // Menghitung total beban kerja setiap tim aktif dari semua proyek mereka
        $employees = Employee::where('status', 'Active')
            ->withSum('projects as total_allocation', 'employee_project.allocation_percentage')
            ->get()
            ->map(function ($employee) {
                // PROTEKSI MUTLAK: Karyawan tanpa proyek (null) dipaksa menjadi angka 0 murni
                $employee->total_allocation = (float) ($employee->total_allocation ?? 0);
                return $employee;
            });

        // Mengelompokkan berdasarkan suhu mesin (persentase mutlak)
        $overloaded = $employees->where('total_allocation', '>', 100)->count();
        $safe = $employees->whereBetween('total_allocation', [50, 100])->count();
        $idle = $employees->where('total_allocation', '<', 50)->count(); 

        return [
            Stat::make('Overloaded Team (>100%)', $overloaded . ' Orang')
                ->description($overloaded > 0 ? 'Bahaya Burnout! Distribusikan ulang beban' : 'Aman Terkendali')
                ->color($overloaded > 0 ? 'danger' : 'success')
                ->descriptionIcon('heroicon-m-exclamation-triangle'),
                
            Stat::make('Optimal Workload (50-100%)', $safe . ' Orang')
                ->description('Beban kerja ideal dan produktif')
                ->color('success')
                ->descriptionIcon('heroicon-m-check-badge'),
                
            Stat::make('Idle / Available (<50%)', $idle . ' Orang')
                ->description('Kapasitas kosong, siap gas proyek baru')
                ->color('warning')
                ->descriptionIcon('heroicon-m-bolt'),
        ];
    }
}