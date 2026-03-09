<?php

namespace App\Filament\Input\Resources;

use App\Filament\Input\Resources\TransactionResource\Pages;
use App\Models\Transaction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;

class TransactionResource extends Resource
{
    protected static ?string $model = Transaction::class;
    public static function canViewAny(): bool
    {
        return auth()->user()->role === 'operator';
    }

    protected static ?string $navigationIcon = 'heroicon-o-minus-circle'; // Ikon minus (uang keluar)
    protected static ?string $navigationLabel = 'Input Pengeluaran'; // Pertegas nama loket
    protected static ?string $pluralModelLabel = 'Input Pengeluaran';
    protected static ?string $slug = 'input-pengeluaran';
    protected static ?string $navigationGroup = '2. FASE FINANCE';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\DatePicker::make('transaction_date')
                    ->label('Tanggal Transaksi')
                    ->default(now())
                    ->required(),
                
                // KUNCI MUTLAK SESUAI SOP: Hanya bisa Expense, dikunci dari layar agar kuli tidak bisa iseng
                Forms\Components\Select::make('type')
                    ->label('Tipe (Arus)')
                    ->options([
                        'expense' => 'Pengeluaran (Expense)',
                    ])
                    ->default('expense')
                    ->disabled() 
                    ->dehydrated() 
                    ->required(),
                    
                Forms\Components\Select::make('category')
                    ->label('Kategori')
                    ->options([
                        'operational' => 'Operasional Kantor (Listrik/Air/Internet)',
                        'salary' => 'Gaji Tim / Fee Freelance',
                        'marketing' => 'Marketing / Meta Ads',
                        'asset' => 'Pembelian Aset / Server / Domain',
                        'other' => 'Lain-lain',
                    ])
                    ->searchable()
                    ->required(),
                
                Forms\Components\TextInput::make('amount')
                    ->label('Nominal')
                    ->prefix('Rp')
                    ->numeric()
                    ->mask(\Filament\Support\RawJs::make('$money($input, \',\', \'.\', 0)'))
                    ->stripCharacters('.')
                    ->minValue(0)
                    ->required(),
                    
                Forms\Components\TextInput::make('description')
                    ->label('Keterangan')
                    ->maxLength(255),
            ]);
    }

    // FUNGSI TABLE DIHAPUS MUTLAK

    public static function getPages(): array
    {
        return [
            'index' => Pages\CreateTransaction::route('/'),
        ];
    }
}