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
    protected static ?string $navigationIcon = 'heroicon-o-globe-alt'; // Ikon diganti jadi globe agar relevan dengan web
    protected static ?string $navigationLabel = 'Meja Admin Billing';
    protected static ?string $navigationGroup = '1. FASE SALES & ADMIN';
    protected static ?int $navigationSort = 3;
    protected static ?string $pluralModelLabel = 'Meja Admin Billing';
    protected static ?string $slug = 'input-aset';

    public static function form(Form $form): Form
    {
        // (Isi Form TETAP SAMA seperti sebelumnya, agar kuli tetap bisa input aset baru semua kategori)
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
                    ->url(),
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
                
                // Indikator Warna Otomatis Jika Hampir Mati (30 Hari)
                Tables\Columns\TextColumn::make('end_date')
                    ->label('Jatuh Tempo')
                    ->date()
                    ->color(fn ($record) => $record->end_date <= now()->addDays(30) ? 'danger' : 'success')
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')->badge(),
                
                // PERHATIKAN: Kolom Value (Rp) dan Credentials Link (Password) DIHAPUS MUTLAK dari layar kuli.
            ])
            ->filters([
                // Filter yang sama dipasang di sini, tapi karena query awal sudah dikunci 'Web', aman.
                Tables\Filters\Filter::make('expiring_soon')
                    ->label('Harus Ditagih Bulan Ini')
                    ->toggle()
                    ->query(fn (Builder $query): Builder => $query->where('end_date', '<=', now()->addDays(30))->where('status', 'active')),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label('Update Tgl'), // Hanya untuk update jika klien sudah bayar
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