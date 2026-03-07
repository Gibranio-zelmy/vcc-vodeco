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
    protected static ?string $heading = '🚨 RADAR BAHAYA: Aset Hampir Mati';

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
                    ->label('Klien'),
                Tables\Columns\TextColumn::make('name')
                    ->label('Aset/Layanan'),
                Tables\Columns\TextColumn::make('category')
                    ->badge(),
                Tables\Columns\TextColumn::make('end_date')
                    ->label('Deadline')
                    ->date()
                    ->color(fn ($record) => 
                        Carbon::parse($record->end_date)->isPast() || Carbon::parse($record->end_date)->diffInDays(now()) <= 7 
                        ? 'danger' : 'warning'
                    )
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('days_left')
                    ->label('Sisa Hari')
                    ->state(function (Asset $record) {
                        $diff = (int) round(Carbon::now()->diffInDays(Carbon::parse($record->end_date), false));
                        return $diff < 0 ? 'LEWAT ' . abs($diff) . ' HARI' : $diff . ' Hari Lagi';
                    })
                    ->color(fn ($state) => str_contains($state, 'LEWAT') ? 'danger' : 'warning'),
            ])
            ->actions([
                // Tombol cepat untuk akses link aset/ads
                Tables\Actions\Action::make('buka_akses')
                    ->label('Buka Akses')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn (Asset $record): string => $record->credentials_link ?? '#')
                    ->openUrlInNewTab()
                    ->hidden(fn (Asset $record) => !$record->credentials_link),
            ]);
    }
}