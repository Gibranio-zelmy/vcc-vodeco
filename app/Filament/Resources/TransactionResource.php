<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TransactionResource\Pages;
use App\Filament\Resources\TransactionResource\RelationManagers;
use App\Models\Transaction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TransactionResource extends Resource
{
    protected static ?string $model = Transaction::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'CASHFLOW';
    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\DatePicker::make('transaction_date')
                    ->label('Tanggal Transaksi')
                    ->default(now())
                    ->required(),
                
                // PERBAIKAN: Menggunakan Select agar jadi Dropdown, bukan TextInput
                Forms\Components\Select::make('type')
                    ->label('Tipe (Arus)')
                    ->options([
                        'income' => 'Pemasukan (Income)',
                        'expense' => 'Pengeluaran (Expense)',
                    ])
                    ->required(),
                    
                // PERBAIKAN: Menggunakan Select untuk Kategori agar seragam dan anti-typo
                Forms\Components\Select::make('category')
                    ->label('Kategori')
                    ->options([
                        'project' => 'Project Klien / Tagihan',
                        'operational' => 'Operasional Kantor (Listrik/Air/Internet)',
                        'salary' => 'Gaji Tim / Fee Freelance',
                        'marketing' => 'Marketing / Meta Ads',
                        'asset' => 'Pembelian Aset / Server / Domain',
                        'other' => 'Lain-lain',
                    ])
                    ->searchable()
                    ->required(),
                
                // PERBAIKAN FORM: Hanya menerima angka murni, mencegah error database
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

    public static function table(Table $table): Table
    {
        return $table
        ->columns([
            \Filament\Tables\Columns\TextColumn::make('transaction_date')
                ->label('Tanggal')
                ->date('d M Y')
                ->sortable(),
                
            \Filament\Tables\Columns\TextColumn::make('type')
                ->label('Arus')
                ->badge()
                ->color(fn (string $state): string => match ($state) {
                    'income' => 'success', // Hijau Terminal
                    'expense' => 'danger', // Merah Peringatan
                    default => 'gray',
                })
                ->formatStateUsing(fn (string $state): string => strtoupper($state)),
                
            \Filament\Tables\Columns\TextColumn::make('category')
                ->label('Kategori')
                ->searchable()
                ->formatStateUsing(fn (string $state): string => strtoupper($state)),
                
            // PERBAIKAN TABEL: Mengganti money('IDR') menjadi format titik bersih tanpa desimal
            \Filament\Tables\Columns\TextColumn::make('amount')
                ->label('Nominal (IDR)')
                ->formatStateUsing(fn ($state) => 'Rp ' . number_format((float)$state, 0, ',', '.'))
                ->alignRight()
                ->sortable(),
                
            \Filament\Tables\Columns\TextColumn::make('description')
                ->label('Keterangan')
                ->limit(30)
                ->searchable(),
        ])
        ->defaultSort('transaction_date', 'desc') // Otomatis selalu tampilkan yang paling baru di atas
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTransactions::route('/'),
            'create' => Pages\CreateTransaction::route('/create'),
            'edit' => Pages\EditTransaction::route('/{record}/edit'),
        ];
    }
}