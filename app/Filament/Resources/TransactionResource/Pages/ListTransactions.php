<?php

namespace App\Filament\Resources\TransactionResource\Pages;

use App\Filament\Resources\TransactionResource;
use App\Filament\Imports\TransactionImporter;
use Filament\Actions;
use Filament\Actions\ImportAction;
use Filament\Resources\Pages\ListRecords;

class ListTransactions extends ListRecords
{
    protected static string $resource = TransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // TOMBOL BARU UNTUK IMPORT EXCEL/CSV
            ImportAction::make()
                ->importer(TransactionImporter::class)
                ->label('Import Data (CSV)')
                ->color('success')
                ->icon('heroicon-o-arrow-down-tray'),
                
            // TOMBOL BAWAAN UNTUK INPUT MANUAL
            Actions\CreateAction::make(),
        ];
    }
}