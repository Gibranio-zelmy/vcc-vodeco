<?php

namespace App\Filament\Input\Resources\InvoiceResource\Pages;

use App\Filament\Input\Resources\InvoiceResource;
use Filament\Resources\Pages\CreateRecord;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Filament\Notifications\Notification; // Pastikan ini dipanggil

class CreateInvoice extends CreateRecord
{
    protected static string $resource = InvoiceResource::class;

    protected function getCreateFormAction(): \Filament\Actions\Action
    {
        return parent::getCreateFormAction()->label('Cetak & Kirim ke Radar AR');
    }

    // 1. MODIFIKASI NOTIFIKASI SUKSES (Layar Operator)
    // Kita ganti title biasa menjadi objek Notification utuh agar bisa dipasang 'persistent'
    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Invoice Berhasil Dicetak!')
            ->body('Radar AR Aging menyala. Sinyal telah ditembakkan ke meja VIP.')
            ->persistent(); // <--- KUNCI MUTLAK: Notifikasi tidak akan hilang sampai di-klik silang (X)
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    // ==========================================
    // SUNTIKAN LONCENG BYPASS: INVOICE TERBIT
    // ==========================================
    protected function afterCreate(): void
    {
        $invoice = $this->record;
        $rupiah = 'Rp ' . number_format($invoice->amount, 0, ',', '.');
        
        // Cari radar Bos
        $admins = User::whereIn('role', ['admin', 'Admin'])->get();

        foreach ($admins as $bos) {
            DB::table('notifications')->insert([
                'id' => (string) Str::uuid(),
                'type' => 'Filament\Notifications\DatabaseNotification',
                'notifiable_type' => 'App\Models\User',
                'notifiable_id' => $bos->id,
                'data' => json_encode([
                    'format' => 'filament',
                    'title' => '📄 INVOICE DITERBITKAN: ' . $rupiah,
                    'body' => 'Tagihan [' . $invoice->invoice_number . '] tercetak. Menunggu pembayaran klien.',
                    'color' => 'info',
                    'icon' => 'heroicon-o-document-text',
                    // Di dalam laci database, notifikasi ini sifatnya 'abadi' 
                    // sampai Bos klik "Mark as Read" di lonceng.
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}