<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OperationalPlatformResource\Pages;
use App\Models\OperationalPlatform;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class OperationalPlatformResource extends Resource
{
    protected static ?string $model = OperationalPlatform::class;

    protected static ?string $navigationIcon = 'heroicon-o-key'; 
    protected static ?string $navigationGroup = 'DATABASE';
    protected static ?string $navigationLabel = 'Master Password';
    protected static ?string $pluralModelLabel = 'Master Platform Eksternal';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Platform')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nama Platform')
                            ->placeholder('Contoh: Niagahoster / Meta Business')
                            ->required(),
                        Forms\Components\TextInput::make('division')
                            ->label('Divisi Pemegang')
                            ->placeholder('Contoh: Tim IT / Marketing'),
                        
                        // PERUBAHAN MUTLAK: Teks Bebas menjadi Dropdown Presisi
                        Forms\Components\Select::make('function')
                            ->label('Kategori / Fungsi Platform')
                            ->options([
                                'Server & Hosting' => 'Server & Hosting (VPS, cPanel, dll)',
                                'Domain Provider' => 'Domain Provider (Registrar)',
                                'Website CMS' => 'Website CMS (WP Admin, Shopify, dll)',
                                'Social Media' => 'Social Media (Instagram, TikTok, Meta)',
                                'Digital Ads' => 'Digital Ads (Google Ads, Meta Ads)',
                                'Email & Workspace' => 'Email & Workspace (Google, Webmail)',
                                'Design & Assets' => 'Design & Assets (Canva, Envato, Figma)',
                                'Marketing & Analytics' => 'Marketing & Analytics (SEO, Mailchimp)',
                                'Payment Gateway' => 'Payment Gateway & Keuangan (Midtrans, dll)',
                                'Lain-lain' => 'Lain-lain',
                            ])
                            ->searchable()
                            ->required()
                            ->columnSpanFull(),
                            
                        Forms\Components\TextInput::make('url')
                            ->label('Link / URL Login')
                            ->url()
                            ->columnSpanFull(),
                    ])->columns(2),

                Forms\Components\Section::make('Akses Kredensial Tertinggi (VIP)')
                    ->schema([
                        Forms\Components\TextInput::make('username')
                            ->label('Username / Email Login'),
                        Forms\Components\TextInput::make('password')
                            ->label('Password Akses')
                            ->password() 
                            ->revealable(), 
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Platform')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('division')
                    ->label('Divisi')
                    ->badge()
                    ->searchable(),
                // Tampilan di tabel disulap menjadi Badge agar elegan
                Tables\Columns\TextColumn::make('function')
                    ->label('Kategori')
                    ->badge()
                    ->color('info')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('username')
                    ->label('Username')
                    ->copyable() 
                    ->copyMessage('Username disalin ke clipboard!'),
                Tables\Columns\TextColumn::make('password')
                    ->label('Password')
                    ->formatStateUsing(fn ($state) => '••••••••') 
                    ->copyableState(fn ($record) => $record->password) 
                    ->copyable()
                    ->copyMessage('Password asli disalin ke clipboard!')
                    ->color('danger'),
                Tables\Columns\TextColumn::make('url')
                    ->label('Akses Web')
                    ->url(fn ($record) => $record->url, true)
                    ->color('primary')
                    ->openUrlInNewTab() 
                    ->icon('heroicon-o-arrow-top-right-on-square'),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOperationalPlatforms::route('/'),
            'create' => Pages\CreateOperationalPlatform::route('/create'),
            'edit' => Pages\EditOperationalPlatform::route('/{record}/edit'),
        ];
    }
}