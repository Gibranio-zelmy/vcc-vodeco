<?php

namespace App\Filament\Input\Resources\ClientResource\Pages;

use App\Filament\Input\Resources\ClientResource;
use Filament\Resources\Pages\CreateRecord;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Filament\Notifications\Notification; // Panggil mesin lonceng

class CreateClient extends CreateRecord
{
    protected static string $resource = ClientResource::class;

    // 1. MODIFIKASI NOTIFIKASI SUKSES (Layar Operator)
    // Mengganti getCreatedNotificationTitle dengan objek yang bisa dikunci (persistent)
    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Klien Berhasil Didaftarkan!')
            ->body('Data klien baru sudah aman di brankas dan sinyal sudah masuk ke radar VIP.')
            ->persistent(); // <--- KUNCI MUTLAK: Tidak akan hilang sendiri
    }

    // INJEKSI LONCENG BYPASS: Radar Klien Baru
    protected function afterCreate(): void
    {
        $klien = $this->record;
        $admins = User::whereIn('role', ['admin', 'Admin'])->get();

        foreach ($admins as $bos) {
            DB::table('notifications')->insert([
                'id' => (string) Str::uuid(),
                'type' => 'Filament\Notifications\DatabaseNotification',
                'notifiable_type' => 'App\Models\User',
                'notifiable_id' => $bos->id,
                'data' => json_encode([
                    'format' => 'filament',
                    'title' => '🤝 KLIEN BARU TERDAFTAR',
                    'body' => "Operator baru saja mendaftarkan: {$klien->name} " . ($klien->company_name ? "({$klien->company_name})" : ""),
                    'color' => 'success',
                    'icon' => 'heroicon-o-user-plus',
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}