<?php

namespace App\Filament\Input\Resources;

use App\Filament\Input\Resources\ClientResource\Pages;
use App\Models\Client;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ClientResource extends Resource
{
    protected static ?string $model = Client::class;
    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationGroup = 'FASE 1: KLIEN & PESANAN';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Dasar')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nama Lengkap')
                            ->required(),
                        Forms\Components\TextInput::make('company_name')
                            ->label('Nama Perusahaan'),
                        Forms\Components\TextInput::make('city')
                            ->label('Asal Kota')
                            ->placeholder('Contoh: Jakarta, Surabaya, dll.'),
                        Forms\Components\DatePicker::make('join_date')
                            ->label('Tgl Bergabung')
                            ->default(now())
                            ->required(),
                    ])->columns(2),

                Forms\Components\Section::make('Kontak & Komunikasi')
                    ->schema([
                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->placeholder('contoh@vodeco.id'),
                        Forms\Components\TextInput::make('whatsapp')
                            ->label('Nomor WhatsApp')
                            ->tel()
                            ->placeholder('0812xxxx')
                            ->prefix('+62'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('client_code')
                    ->label('ID Klien')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('primary'),
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Klien')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('company_name')
                    ->label('Perusahaan')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('city')
                    ->label('Asal Kota')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('gray'),
                Tables\Columns\TextColumn::make('whatsapp')
                    ->label('WhatsApp'),
                Tables\Columns\TextColumn::make('join_date')
                    ->label('Tgl Bergabung')
                    ->date()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\RestoreAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListClients::route('/'),
            'create' => Pages\CreateClient::route('/create'),
            'edit' => Pages\EditClient::route('/{record}/edit'),
        ];
    }
}