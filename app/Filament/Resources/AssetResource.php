<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AssetResource\Pages;
use App\Filament\Resources\AssetResource\RelationManagers;
use App\Models\Asset;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AssetResource extends Resource
{
    protected static ?string $model = Asset::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'DATABASE';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Klasifikasi Layanan Vodeco')
                    ->schema([
                        Forms\Components\Select::make('client_id')
                            ->relationship('client', 'name')
                            ->searchable()
                            ->required(),
                        Forms\Components\TextInput::make('name')
                            ->label('Nama Aset/Layanan')
                            ->required(),
                        Forms\Components\Select::make('category')
                            ->options([
                                'SEO' => 'SEO Specialist',
                                'Design' => 'Creative Design (Logo/Compro)',
                                'Marketing' => 'Digital Ads (Meta/Google)',
                                'Web' => 'Website Asset (Domain/Hosting)',
                            ])->required(),
                        Forms\Components\TextInput::make('platform')
                            ->placeholder('Meta/Google/Drive/Figma'),
                    ])->columns(2),

                Forms\Components\Section::make('Timeline & Value')
                    ->schema([
                        Forms\Components\DatePicker::make('start_date'),
                        Forms\Components\DatePicker::make('end_date')
                            ->label('Deadline / Expiry'),
                            
                        // PERBAIKAN FORM: Angka murni dengan Prefix Rp untuk mencegah error database
                        Forms\Components\TextInput::make('value')
                            ->label('Value (IDR)')
                            ->prefix('Rp')
                            ->numeric()
                            ->mask(\Filament\Support\RawJs::make('$money($input, \',\', \'.\', 0)'))
                            ->stripCharacters('.')
                            ->required()
                            ->minValue(0),
                            
                        Forms\Components\Select::make('status')
                            ->options([
                                'active' => 'Active', 
                                'completed' => 'Completed', 
                                'expired' => 'Expired'
                            ])
                            ->required(),
                    ])->columns(2),

                Forms\Components\TextInput::make('credentials_link')
                    ->label('Link Akses/Asset')
                    ->url(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('client.name')
                    ->label('Klien')
                    ->searchable()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('name')
                    ->label('Aset/Layanan')
                    ->searchable(),
                    
                Tables\Columns\TextColumn::make('category')
                    ->badge()
                    ->color('primary'),
                
                // PERBAIKAN TABEL: Mengunci format Rp, rata kanan, tanpa desimal
                Tables\Columns\TextColumn::make('value')
                    ->label('Value (IDR)')
                    ->formatStateUsing(fn ($state) => 'Rp ' . number_format((float)$state, 0, ',', '.'))
                    ->alignRight() 
                    ->sortable(),

                Tables\Columns\TextColumn::make('credentials_link')
                    ->label('Akses')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->color('info')
                    ->url(fn ($record) => $record->credentials_link)
                    ->openUrlInNewTab()
                    ->placeholder('No Link'),

                Tables\Columns\TextColumn::make('end_date')
                    ->label('Deadline')
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->colors([
                        'success' => 'active',
                        'warning' => 'completed',
                        'danger' => 'expired',
                    ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\Filter::make('expiring_soon')
                    ->label('Mati dalam 30 Hari ⚠️')
                    ->toggle()
                    ->query(fn (Builder $query): Builder => $query->where('end_date', '<=', now()->addDays(30))->where('status', 'active')),
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
            'index' => Pages\ListAssets::route('/'),
            'create' => Pages\CreateAsset::route('/create'),
            'edit' => Pages\EditAsset::route('/{record}/edit'),
        ];
    }
}