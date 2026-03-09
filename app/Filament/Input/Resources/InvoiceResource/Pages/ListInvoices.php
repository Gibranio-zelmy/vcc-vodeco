<?php

namespace App\Filament\Input\Resources\InvoiceResource\Pages;

use App\Filament\Input\Resources\InvoiceResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListInvoices extends ListRecords
{
    protected static string $resource = InvoiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Cetak Invoice Baru'), // Tombol di pojok kanan atas
        ];
    }
}