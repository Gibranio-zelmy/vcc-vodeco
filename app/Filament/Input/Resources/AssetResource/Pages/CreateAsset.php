<?php

namespace App\Filament\Input\Resources\AssetResource\Pages;

use App\Filament\Input\Resources\AssetResource;
use Filament\Resources\Pages\CreateRecord;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Filament\Notifications\Notification; // Panggil mesin lonceng

class CreateAsset extends CreateRecord
{
    protected static string $resource = AssetResource::class;

    protected function getCreateFormAction(): \Filament\Actions\Action
    {
        return parent::getCreateFormAction()->label('Simpan Data Aset');
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    // 1. MODIFIKASI NOTIFIKASI SUKSES (Layar Operator)
    // Mengganti title biasa menjadi objek yang Bandel (Persistent)
    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Aset Berhasil Disimpan!')
            ->body('Data aset/layanan telah terdaftar dan sinyal sudah masuk ke radar VIP.')
            ->persistent(); // <--- KUNCI MUTLAK: Tidak akan hilang sampai di-klik silang (X)
    }

    // ==========================================
    // SUNTIKAN LONCENG BYPASS: RADAR ASET BARU
    // ==========================================
    protected function afterCreate(): void
    {
        $aset = $this->record;
        $admins = User::whereIn('role', ['admin', 'Admin'])->get();

        foreach ($admins as $bos) {
            DB::table('notifications')->insert([
                'id' => (string) Str::uuid(),
                'type' => 'Filament\Notifications\DatabaseNotification',
                'notifiable_type' => 'App\Models\User',
                'notifiable_id' => $bos->id,
                'data' => json_encode([
                    'format' => 'filament',
                    'title' => '📦 ASET/LAYANAN BARU',
                    'body' => "Kategori [{$aset->category}] baru saja didaftarkan: {$aset->name} untuk klien {$aset->client->name}.",
                    'color' => 'info',
                    'icon' => 'heroicon-o-cube',
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}