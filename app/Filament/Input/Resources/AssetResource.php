<?php

namespace App\Filament\Input\Resources;

use App\Filament\Input\Resources\AssetResource\Pages;
use App\Models\Asset;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AssetResource extends Resource
{
    protected static ?string $model = Asset::class;

    public static function canViewAny(): bool
    {
        return auth()->user()->role === 'operator';
    }

    protected static ?string $navigationIcon = 'heroicon-o-globe-alt';
    protected static ?string $navigationLabel = 'Meja Admin Billing';
    protected static ?string $navigationGroup = 'FASE 1: KLIEN & PESANAN';
    protected static ?int $navigationSort = 1;
    protected static ?string $pluralModelLabel = 'Meja Admin Billing';
    protected static ?string $slug = 'input-aset';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Klasifikasi Layanan Vodeco')
                    ->description('Input data layanan/aset klien untuk dimonitor oleh Radar VIP.')
                    ->schema([
                        Forms\Components\Select::make('client_id')
                            ->relationship('client', 'name')
                            ->searchable()
                            ->required(),
                        Forms\Components\TextInput::make('name')
                            ->label('Nama Aset/Layanan')
                            ->required(),
                        Forms\Components\Select::make('category')
                            ->label('Kategori Layanan')
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
                        Forms\Components\TextInput::make('value')
                            ->label('Value (IDR)')
                            ->prefix('Rp')
                            ->numeric()
                            ->mask(\Filament\Support\RawJs::make('$money($input, \',\', \'.\', 0)'))
                            ->stripCharacters('.')
                            ->minValue(0),
                        Forms\Components\Select::make('status')
                            ->options([
                                'active' => 'Active', 
                                'completed' => 'Completed', 
                                'expired' => 'Expired'
                            ])
                            ->default('active')
                            ->required(),
                    ])->columns(2),

                Forms\Components\TextInput::make('credentials_link')
                    ->label('Link Akses/Asset')
                    ->url()
                    ->helperText('Akses ini hanya akan terlihat oleh akun VIP.'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(
                // KUNCI MUTLAK: Kuli HANYA bisa melihat aset kategori 'Web' (Domain/Hosting)
                Asset::query()->where('category', 'Web')
            )
            ->columns([
                Tables\Columns\TextColumn::make('client.name')->label('Klien')->searchable(),
                Tables\Columns\TextColumn::make('name')->label('Domain / Hosting')->searchable(),
                
                Tables\Columns\TextColumn::make('end_date')
                    ->label('Jatuh Tempo')
                    ->date('d M Y')
                    ->color(fn ($record) => $record->end_date <= now()->addDays(30) ? 'danger' : 'success')
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')->badge(),
            ])
            ->filters([
                Tables\Filters\Filter::make('expiring_soon')
                    ->label('Harus Ditagih Bulan Ini')
                    ->toggle()
                    ->query(fn (Builder $query): Builder => $query->where('end_date', '<=', now()->addDays(30))->where('status', 'active')),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label('Update Tgl'),
            ]);
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