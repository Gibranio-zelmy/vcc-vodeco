<?php

namespace App\Filament\Resources\CompanyDocumentResource\Pages;

use App\Filament\Resources\CompanyDocumentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCompanyDocument extends EditRecord
{
    protected static string $resource = CompanyDocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
