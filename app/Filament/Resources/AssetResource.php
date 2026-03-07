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

    public static function form(Form $form): Form
{
    return $form
        ->schema([
            Forms\Components\Section::make('Klasifikasi Layanan Vodeco')
                ->schema([
                    Forms\Components\Select::make('client_id')
                        ->relationship('client', 'name')->searchable()->required(),
                    Forms\Components\TextInput::make('name')->label('Nama Aset/Layanan')->required(),
                    Forms\Components\Select::make('category')
                        ->options([
                            'SEO' => 'SEO Specialist',
                            'Design' => 'Creative Design (Logo/Compro)',
                            'Marketing' => 'Digital Ads (Meta/Google)',
                            'Web' => 'Website Asset (Domain/Hosting)',
                        ])->required(),
                    Forms\Components\TextInput::make('platform')->placeholder('Meta/Google/Drive/Figma'),
                ])->columns(2),

            Forms\Components\Section::make('Timeline & Value')
                ->schema([
                    Forms\Components\DatePicker::make('start_date'),
                    Forms\Components\DatePicker::make('end_date')->label('Deadline / Expiry'),
                    Forms\Components\TextInput::make('value')->numeric()->prefix('IDR'),
                    Forms\Components\Select::make('status')
                        ->options(['active' => 'Active', 'completed' => 'Completed', 'expired' => 'Expired'])
                        ->required(),
                ])->columns(2),

            Forms\Components\TextInput::make('credentials_link')->label('Link Akses/Asset')->url(),
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
            // Menggunakan TextColumn + badge() karena lebih fleksibel di Filament v3
            Tables\Columns\TextColumn::make('category')
                ->badge()
                ->color('primary'),
            
            // UPDATE: Menampilkan Nilai Proyek/Aset
            Tables\Columns\TextColumn::make('value')
                ->label('Value (IDR)')
                ->money('idr') 
                ->sortable(),

            // UPDATE: Link Akses yang bisa diklik langsung
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
        ->defaultSort('created_at', 'desc') // Terbaru selalu di atas
        ->filters([])
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
