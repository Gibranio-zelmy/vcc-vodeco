<?php

namespace App\Filament\Widgets;

use App\Models\Asset;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Carbon\Carbon;

class AssetAlertsWidget extends BaseWidget
{
    // Menaruh radar di urutan pertama paling atas
    protected static ?int $sort = 1;
    
    // Memberi nama radar
    protected static ?string $heading = '🚨 RADAR BAHAYA: Aset & Layanan Hampir Mati';

    // Membuat widget memakan lebar penuh layar agar jelas terlihat
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                // Hanya mengambil aset yang Aktif dan akan mati dalam 30 hari ke depan
                Asset::query()
                    ->where('status', 'active')
                    ->where('end_date', '<=', Carbon::now()->addDays(30))
                    ->orderBy('end_date', 'asc')
            )
            ->columns([
                Tables\Columns\TextColumn::make('client.name')
                    ->label('Klien')
                    ->searchable(),
                    
                Tables\Columns\TextColumn::make('name')
                    ->label('Aset / Layanan')
                    ->searchable(),
                    
                Tables\Columns\TextColumn::make('category')
                    ->label('Kategori')
                    ->badge()
                    ->color('primary'),
                    
                Tables\Columns\TextColumn::make('end_date')
                    ->label('Deadline')
                    ->date('d M Y') // Format tanggal presisi
                    ->color(function ($record) {
                        // Kunci waktu mutlak di jam 00:00 agar tidak ada pecahan desimal
                        $end = Carbon::parse($record->end_date)->startOfDay();
                        $today = now()->startOfDay();
                        
                        // Merah menyala jika sudah lewat, jatuh pada hari ini, atau sisa <= 7 hari
                        if ($end->isBefore($today) || $end->isSameDay($today) || $today->diffInDays($end) <= 7) {
                            return 'danger';
                        }
                        return 'warning'; // Kuning jika masih di atas 7 hari (tapi di bawah 30)
                    })
                    ->weight('bold'),
                    
                // PERBAIKAN MUTLAK: Hitungan hari murni tanpa desimal
                Tables\Columns\TextColumn::make('days_left')
                    ->label('Sisa Waktu')
                    ->state(function (Asset $record) {
                        $end = Carbon::parse($record->end_date)->startOfDay();
                        $today = now()->startOfDay();
                        
                        if ($end->isBefore($today)) {
                            return 'LEWAT ' . (int) $today->diffInDays($end) . ' HARI';
                        } elseif ($end->isSameDay($today)) {
                            return 'HARI INI DEADLINE';
                        } else {
                            return (int) $today->diffInDays($end) . ' HARI LAGI';
                        }
                    })
                    ->badge() // Diubah jadi badge agar sangat mencolok di mata
                    ->color(fn ($state) => str_contains($state, 'LEWAT') || str_contains($state, 'HARI INI') ? 'danger' : 'warning'),
            ])
            ->actions([
                // Tombol cepat untuk akses link aset/ads
                Tables\Actions\Action::make('buka_akses')
                    ->label('Buka Akses')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->color('info')
                    ->url(fn (Asset $record): string => $record->credentials_link ?? '#')
                    ->openUrlInNewTab()
                    ->hidden(fn (Asset $record) => !$record->credentials_link), // Sembunyi jika tidak ada link
            ]);
    }
}