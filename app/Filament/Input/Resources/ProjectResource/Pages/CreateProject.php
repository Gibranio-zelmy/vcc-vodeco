<?php

namespace App\Filament\Input\Resources\ProjectResource\Pages;

use App\Filament\Input\Resources\ProjectResource;
use Filament\Resources\Pages\CreateRecord;

class CreateProject extends CreateRecord
{
    protected static string $resource = ProjectResource::class;

    protected function getCreateFormAction(): \Filament\Actions\Action
    {
        return parent::getCreateFormAction()
            ->label('Kirim Proyek ke Radar VIP');
    }

    // PANTULAN MUTLAK: Kembali ke form kosong
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Proyek berhasil masuk ke antrean Radar!';
    }
}