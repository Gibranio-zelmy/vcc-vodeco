<?php

namespace App\Filament\Input\Resources\InvoiceResource\Pages;

use App\Filament\Input\Resources\InvoiceResource;
use Filament\Resources\Pages\CreateRecord;

class CreateInvoice extends CreateRecord
{
    protected static string $resource = InvoiceResource::class;

    protected function getCreateFormAction(): \Filament\Actions\Action
    {
        // Teks tombol dipertahankan sesuai standar SOP
        return parent::getCreateFormAction()->label('Cetak & Kirim ke Radar AR');
    }

    // PANTULAN MUTLAK: Setelah sukses, kembali ke Meja Kasir (Daftar Tagihan)
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        // Notifikasi dipertahankan
        return 'Invoice berhasil dicetak! Radar AR Aging menyala.';
    }
}