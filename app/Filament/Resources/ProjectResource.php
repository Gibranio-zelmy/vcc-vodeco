<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProjectResource\Pages;
use App\Filament\Resources\ProjectResource\RelationManagers;
use App\Models\Project;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProjectResource extends Resource
{
    protected static ?string $model = Project::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'OPERATIONS';
    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nama Proyek')
                    ->required()
                    ->maxLength(255),
                    
                    Forms\Components\Select::make('client_id')
                    ->label('Nama Klien')
                    ->relationship('client', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                    
                // PERBAIKAN FORM 1: Dropdown Tipe Proyek
                Forms\Components\Select::make('type')
                    ->label('Tipe Proyek')
                    ->options([
                        'Website' => 'Website Development',
                        'Ads' => 'Digital Ads (Meta/Google)',
                        'Design' => 'Creative Design (Logo/UI)',
                        'SEO' => 'SEO Specialist',
                        'Maintenance' => 'Maintenance / Server',
                    ])
                    ->searchable()
                    ->required(),
                    
                // PERBAIKAN FORM 2: Dropdown Status Pengerjaan
                Forms\Components\Select::make('status')
                    ->label('Status Pengerjaan')
                    ->options([
                        'Queue' => 'Antrean (Queue)',
                        'In Progress' => 'Sedang Dikerjakan (In Progress)',
                        'Review' => 'Menunggu Review / Revisi',
                        'Completed' => 'Selesai (Completed)',
                    ])
                    ->default('Queue')
                    ->required(),
                
                // PERBAIKAN FORM INPUT (Hanya Angka Murni & Prefix Rp)
                Forms\Components\TextInput::make('project_value')
                    ->label('Nilai Proyek')
                    ->prefix('Rp')
                    ->numeric()
                    ->mask(\Filament\Support\RawJs::make('$money($input, \',\', \'.\', 0)'))
                    ->stripCharacters('.')
                    ->minValue(0)
                    ->required(),
                
                Forms\Components\DatePicker::make('start_date')
                    ->label('Tanggal Mulai'),
                    
                Forms\Components\DatePicker::make('deadline')
                    ->label('Tenggat Waktu (Deadline)'),
                    
                \Filament\Forms\Components\Repeater::make('employees')
                    ->relationship()
                    ->schema([
                        \Filament\Forms\Components\Select::make('employee_id')
                            ->options(\App\Models\Employee::pluck('name', 'id'))
                            ->required()
                            ->label('Pilih Operator (Tim)'),
                            
                        \Filament\Forms\Components\TextInput::make('allocation_percentage')
                            ->numeric()
                            ->default(100)
                            ->required()
                            ->label('Beban Kerja (%)')
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Proyek')
                    ->searchable(),
                    
                Tables\Columns\TextColumn::make('client.name')
                    ->label('Klien')
                    ->searchable()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('type')
                    ->label('Tipe')
                    ->searchable(),
                    
                // PERBAIKAN TABEL: Tambahan Badge Warna untuk Status
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Queue' => 'gray',
                        'In Progress' => 'warning',
                        'Review' => 'info',
                        'Completed' => 'success',
                        default => 'primary',
                    })
                    ->searchable(),
                
                // PERBAIKAN TABEL (Format Titik & Rata Kanan)
                Tables\Columns\TextColumn::make('project_value')
                    ->label('Nilai Proyek')
                    ->formatStateUsing(fn ($state) => 'Rp ' . number_format((float)$state, 0, ',', '.'))
                    ->alignRight()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('start_date')
                    ->label('Mulai')
                    ->date('d M Y')
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('deadline')
                    ->label('Deadline')
                    ->date('d M Y')
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                    
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
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
            'index' => Pages\ListProjects::route('/'),
            'create' => Pages\CreateProject::route('/create'),
            'edit' => Pages\EditProject::route('/{record}/edit'),
        ];
    }
}