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

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\DatePicker::make('transaction_date')
                    ->label('Tanggal Transaksi')
                    ->default(now())
                    ->required(),
                Forms\Components\TextInput::make('type')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('category')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('amount')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('description')
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
                ->searchable(),
                
            \Filament\Tables\Columns\TextColumn::make('amount')
                ->label('Nominal (IDR)')
                ->money('IDR', locale: 'id') // Format otomatis Rupiah
                ->sortable()
                ->alignment(\Filament\Support\Enums\Alignment::End), // Rata kanan presisi tinggi
                
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
