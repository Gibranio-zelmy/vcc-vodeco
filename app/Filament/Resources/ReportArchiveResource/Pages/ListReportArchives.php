<?php

namespace App\Filament\Resources\ReportArchiveResource\Pages;

use App\Filament\Resources\ReportArchiveResource;
use Filament\Actions;
use Filament\Forms\Components\Select;
use Filament\Resources\Pages\ListRecords;
use App\Models\ReportArchive;

class ListReportArchives extends ListRecords
{
    protected static string $resource = ReportArchiveResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('generate_new')
                ->label('Generate Laporan Baru')
                ->icon('heroicon-o-document-plus')
                ->color('primary')
                ->form([
                    Select::make('month')
                        ->label('Bulan')
                        ->options([
                            '01' => 'Januari', '02' => 'Februari', '03' => 'Maret',
                            '04' => 'April', '05' => 'Mei', '06' => 'Juni',
                            '07' => 'Juli', '08' => 'Agustus', '09' => 'September',
                            '10' => 'Oktober', '11' => 'November', '12' => 'Desember',
                        ])->default(now()->format('m'))->required(),
                    Select::make('year')
                        ->label('Tahun')
                        ->options(array_combine(range(2022, now()->year), range(2022, now()->year)))
                        ->default(now()->year)->required(),
                ])
                ->action(function (array $data) {
                    // 1. Catat di Database (Histori)
                    ReportArchive::create([
                        'title' => "Laporan Eksekutif Vodeco - {$data['month']}/{$data['year']}",
                        'month' => $data['month'],
                        'year' => $data['year'],
                    ]);

                    // 2. Langsung Download PDF-nya
                    return ReportArchiveResource::generatePdf($data['month'], $data['year']);
                }),
        ];
    }
}