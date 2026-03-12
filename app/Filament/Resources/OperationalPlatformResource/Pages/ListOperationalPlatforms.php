<?php

namespace App\Filament\Resources\OperationalPlatformResource\Pages;

use App\Filament\Resources\OperationalPlatformResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListOperationalPlatforms extends ListRecords
{
    protected static string $resource = OperationalPlatformResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
