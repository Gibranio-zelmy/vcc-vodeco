<?php

namespace App\Filament\Resources\OperationalPlatformResource\Pages;

use App\Filament\Resources\OperationalPlatformResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditOperationalPlatform extends EditRecord
{
    protected static string $resource = OperationalPlatformResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
