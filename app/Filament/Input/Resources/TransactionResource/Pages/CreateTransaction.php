<?php

namespace App\Filament\Input\Resources\TransactionResource\Pages;

use App\Filament\Input\Resources\TransactionResource;
use Filament\Resources\Pages\CreateRecord;

class CreateTransaction extends CreateRecord
{
    protected static string $resource = TransactionResource::class;

    protected function getCreateFormAction(): \Filament\Actions\Action
    {
        // Ubah teks tombol sesuai fungsi barunya
        return parent::getCreateFormAction()->label('Catat Pengeluaran (Burn Rate)');
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Pengeluaran tercatat! Burn Rate di Radar VIP telah di-update.';
    }
}