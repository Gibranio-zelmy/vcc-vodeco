<?php

namespace App\Filament\Input\Resources;

use App\Filament\Input\Resources\ClientResource\Pages;
use App\Models\Client;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;

class ClientResource extends Resource
{
    protected static ?string $model = Client::class;

    public static function canViewAny(): bool
    {
        return auth()->user()->role === 'operator';
    }

    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationLabel = 'Input Klien Baru';
    protected static ?string $pluralModelLabel = 'Input Klien';
    protected static ?string $slug = 'input-klien';
    protected static ?string $navigationGroup = '1. FASE FINANCE, SERVER & ADMIN BILLING';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Dasar')
                    ->description('Pastikan data klien utama sudah benar sebelum dikirim ke radar VIP.')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nama Lengkap')
                            ->required(),
                        Forms\Components\TextInput::make('company_name')
                            ->label('Nama Perusahaan'),
                        Forms\Components\DatePicker::make('join_date')
                            ->label('Tanggal Bergabung')
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

    // FUNGSI TABLE DIHAPUS MUTLAK - Hanya untuk Input Data

    public static function getPages(): array
    {
        return [
            'index' => Pages\CreateClient::route('/'), // Fokus terkunci ke Form Create
        ];
    }
}