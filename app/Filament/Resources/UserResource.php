<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;

class UserResource extends Resource
{
    protected static ?string $model = User::class;
    protected static ?string $navigationIcon = 'heroicon-o-shield-check';
    protected static ?string $navigationGroup = 'SISTEM KEAMANAN';
    protected static ?string $navigationLabel = 'Akses Login (Users)';

    // GEMBOK MUTLAK: Hanya kasta 'admin' yang bisa melihat menu ini
    public static function canViewAny(): bool
    {
        return auth()->user()->role === 'admin';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nama Pengguna')
                    ->required(),
                
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->required()
                    ->unique(ignoreRecord: true),
                
                // SISTEM ENKRIPSI PASSWORD OTOMATIS
                Forms\Components\TextInput::make('password')
                    ->password()
                    ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                    ->dehydrated(fn ($state) => filled($state))
                    ->required(fn (string $context): bool => $context === 'create')
                    ->label('Password Akses'),
                
                Forms\Components\Select::make('role')
                    ->label('Kasta / Role Akses')
                    ->options([
                        'admin' => 'VIP (Bos)',
                        'hrd' => 'Komandan HRD',
                        'operator' => 'Kuli Uang & Data (Operator)',
                        'karyawan' => 'Kuli Produksi (Desainer, Web, dll)',
                    ])
                    ->required()
                    ->default('karyawan'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama')
                    ->searchable(),
                    
                Tables\Columns\TextColumn::make('email')
                    ->label('Email Akses')
                    ->searchable(),
                    
                Tables\Columns\TextColumn::make('role')
                    ->label('Role Akses')
                    ->badge()
                    ->colors([
                        'danger' => 'admin',
                        'warning' => 'hrd',
                        'info' => 'operator',
                        'success' => 'karyawan',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}