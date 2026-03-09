<?php

namespace App\Filament\Resources\LogHistoryResource\Pages;

use App\Filament\Resources\LogHistoryResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditLogHistory extends EditRecord
{
    protected static string $resource = LogHistoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
