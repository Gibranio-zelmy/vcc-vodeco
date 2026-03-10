<?php

namespace App\Filament\Input\Resources\CompanyDocumentResource\Pages;

use App\Filament\Input\Resources\CompanyDocumentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCompanyDocuments extends ListRecords
{
    protected static string $resource = CompanyDocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
