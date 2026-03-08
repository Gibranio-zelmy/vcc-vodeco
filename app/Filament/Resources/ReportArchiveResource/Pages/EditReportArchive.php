<?php

namespace App\Filament\Resources\ReportArchiveResource\Pages;

use App\Filament\Resources\ReportArchiveResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditReportArchive extends EditRecord
{
    protected static string $resource = ReportArchiveResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
