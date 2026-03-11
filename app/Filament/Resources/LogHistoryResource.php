<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LogHistoryResource\Pages;
use App\Models\LogHistory;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class LogHistoryResource extends Resource
{
    protected static ?string $model = LogHistory::class;
    
    // UBAH MUTLAK: Ikon dan Label yang lebih rapi dan elegan
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationGroup = 'ANALYTICS';
    protected static ?int $navigationSort = 100;
    protected static ?string $navigationLabel = 'Log Historis';
    protected static ?string $pluralModelLabel = 'Daftar Log Historis';

    // KUNCI MUTLAK: CCTV tidak boleh dimanipulasi (Read-Only)
    public static function canCreate(): bool { return false; }
    
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Pelaku Input')
                    ->weight('bold')
                    ->searchable(),
                    
                Tables\Columns\TextColumn::make('action')
                    ->label('Aksi')
                    ->badge()
                    ->colors([
                        'success' => 'CREATE',
                        'warning' => 'UPDATE',
                        'danger' => 'DELETE',
                    ]),
                    
                Tables\Columns\TextColumn::make('model_type')
                    ->label('Target Modul')
                    ->searchable(),
                    
                // Menampilkan jam masuk dan jam keluar data
                Tables\Columns\TextColumn::make('entry_timestamp')
                    ->label('Jam Entry')
                    ->dateTime('d M Y, H:i:s')
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('exit_timestamp')
                    ->label('Jam Exit')
                    ->dateTime('d M Y, H:i:s')
                    ->sortable(),
            ])
            ->defaultSort('exit_timestamp', 'desc')
            ->actions([]) // Hapus tombol edit/delete
            ->bulkActions([]); // Hapus fitur hapus massal
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLogHistories::route('/'),
        ];
    }
}
