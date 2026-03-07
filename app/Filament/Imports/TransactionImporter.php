<?php

namespace App\Filament\Imports;

use App\Models\Transaction;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;

class TransactionImporter extends Importer
{
    protected static ?string $model = Transaction::class;

    public static function getColumns(): array
{
    return [
        ImportColumn::make('type')
            ->requiredMapping()
            ->rules(['required', 'string'])
            ->example('Income (Wajib ketik: Income atau Expense)'),
        
        ImportColumn::make('category')
            ->requiredMapping()
            ->rules(['required', 'string'])
            ->example('Marketing (Bebas: Operasional, Project, Pajak, dll)'),
            
        ImportColumn::make('amount')
            ->requiredMapping()
            ->numeric()
            ->rules(['required', 'numeric'])
            ->example('5000000 (Wajib angka murni, TANPA TITIK DAN KOMA)'),
            
        ImportColumn::make('transaction_date')
            ->requiredMapping()
            ->rules(['required', 'date'])
            ->example('2022-03-15 (Wajib format kalender: Tahun-Bulan-Tanggal)'),
    ];
}

    public function resolveRecord(): ?Transaction
    {
        // Sistem akan membuat baris data baru untuk tiap baris Excel
        return new Transaction();
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Import data Vodeco selesai! ' . number_format($import->successful_rows) . ' baris berhasil masuk.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' Namun, ' . number_format($failedRowsCount) . ' baris gagal karena format salah.';
        }

        return $body;
    }
}