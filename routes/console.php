<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Models\Invoice;
use App\Models\Asset;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

// ==========================================
// ROBOT PATROLI VCC (BANGUN TIAP JAM 00:00)
// ==========================================
Schedule::call(function () {
    // 1. Kunci Target Radar (Hanya ke meja VIP Bos)
    $admins = User::whereIn('role', ['admin', 'Admin'])->get();

    // ----------------------------------------------------------------
    // MISI 1: SIDAK AR AGING (TAGIHAN BOCOR / LEWAT JATUH TEMPO)
    // ----------------------------------------------------------------
    $overdueInvoices = Invoice::with('client')
        ->whereIn('status', ['Unpaid', 'Partial'])
        ->whereDate('due_date', '<', Carbon::today())
        ->get();

    foreach ($overdueInvoices as $invoice) {
        $sisa = 'Rp ' . number_format((float)($invoice->amount - $invoice->paid_amount), 0, ',', '.');
        
        foreach ($admins as $bos) {
            DB::table('notifications')->insert([
                'id' => (string) Str::uuid(),
                'type' => 'Filament\Notifications\DatabaseNotification',
                'notifiable_type' => 'App\Models\User',
                'notifiable_id' => $bos->id,
                'data' => json_encode([
                    'format' => 'filament',
                    'title' => '🚨 TAGIHAN BOCOR: ' . $invoice->invoice_number,
                    'body' => 'Tagihan Klien [' . ($invoice->client->name ?? 'Unknown') . '] senilai ' . $sisa . ' SUDAH LEWAT JATUH TEMPO! Segera suruh Finance tagih hari ini.',
                    'color' => 'danger',
                    'icon' => 'heroicon-o-exclamation-triangle',
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    // ----------------------------------------------------------------
    // MISI 2: SIDAK ASET KRITIS (DOMAIN/HOSTING HAMPIR MATI)
    // ----------------------------------------------------------------
    $expiringAssets = Asset::with('client')
        ->where('category', 'Web')
        ->where('status', 'active')
        ->whereDate('end_date', '<=', Carbon::today()->addDays(30))
        ->get();

    foreach ($expiringAssets as $asset) {
        foreach ($admins as $bos) {
            DB::table('notifications')->insert([
                'id' => (string) Str::uuid(),
                'type' => 'Filament\Notifications\DatabaseNotification',
                'notifiable_type' => 'App\Models\User',
                'notifiable_id' => $bos->id,
                'data' => json_encode([
                    'format' => 'filament',
                    'title' => '🔥 ASET KRITIS: ' . $asset->name,
                    'body' => 'Domain/Hosting Klien [' . ($asset->client->name ?? 'Unknown') . '] akan mati kurang dari 30 hari (' . Carbon::parse($asset->end_date)->format('d M Y') . '). Suruh tim amankan!',
                    'color' => 'warning',
                    'icon' => 'heroicon-o-fire',
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

})->dailyAt('00:00'); // KUNCI MUTLAK: Robot dieksekusi setiap tengah malam