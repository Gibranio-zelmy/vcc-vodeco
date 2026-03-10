<?php

namespace App\Filament\Input\Resources\LeaveRequestResource\Pages;

use App\Filament\Input\Resources\LeaveRequestResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CreateLeaveRequest extends CreateRecord
{
    protected static string $resource = LeaveRequestResource::class;

    // SUNTIKAN INJEKSI MUTLAK (Bypass Langsung ke PostgreSQL)
    protected function afterCreate(): void
    {
        // 1. Radar mencari pasukan HRD dan VIP
        $pasukanTarget = User::whereIn('role', ['hrd', 'HRD', 'admin', 'Admin'])->get();

        // 2. Tembakkan peluru injeksi satu per satu ke laci mereka
        foreach ($pasukanTarget as $target) {
            DB::table('notifications')->insert([
                'id' => (string) Str::uuid(),
                'type' => 'Filament\Notifications\DatabaseNotification',
                'notifiable_type' => 'App\Models\User',
                'notifiable_id' => $target->id,
                'data' => json_encode([
                    'format' => 'filament',
                    'title' => '🔔 Cuti Baru: ' . auth()->user()->name,
                    'body' => 'Ada pengajuan cuti baru yang menunggu untuk diperiksa.',
                    'color' => 'warning',
                    'icon' => 'heroicon-o-calendar-days',
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 3. Pop-up Hijau untuk menenangkan Kuli di layar mereka
        Notification::make()
            ->title('Sinyal Terkirim!')
            ->body('Pengajuan cuti Anda telah ditembakkan langsung ke layar HRD & VIP.')
            ->success()
            ->persistent() 
            ->send();
    }

    // Lempar kuli kembali ke tabel setelah submit
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}