<?php

namespace App\Filament\Resources\LogHistoryResource\Pages;

use App\Filament\Resources\LogHistoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListLogHistories extends ListRecords
{
    protected static string $resource = LogHistoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
