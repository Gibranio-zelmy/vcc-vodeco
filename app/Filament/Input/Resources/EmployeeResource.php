<?php

namespace App\Filament\Input\Resources;

use App\Filament\Input\Resources\EmployeeResource\Pages;
use App\Models\Employee;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class EmployeeResource extends Resource
{
    protected static ?string $model = Employee::class;
    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationGroup = 'HRD & MANPOWER';
    protected static ?string $navigationLabel = 'Data Karyawan';

    // GEMBOK MUTLAK: HANYA HRD YANG BISA MELIHAT MENU INI
    public static function canViewAny(): bool
    {
        return auth()->user()->role === 'hrd';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nama Karyawan')
                    ->required(),
                
                Forms\Components\Select::make('role')
                    ->label('Posisi / Jabatan')
                    ->options([
                        'Customer Service' => 'Customer Service',
                        'Web Designer' => 'Web Designer',
                        'Finance' => 'Finance',
                        'GA Staff' => 'GA Staff',
                        'HRD' => 'HRD',
                        'Social Media Specialist' => 'Social Media Specialist',
                        'Ads Specialist' => 'Ads Specialist',
                        'SEO Specialist' => 'SEO Specialist',
                        'Technical Support' => 'Technical Support',
                        'Admin Billing' => 'Admin Billing',
                        'Full Stack Programmer' => 'Full Stack Programmer',
                        'Paid Intern' => 'Paid Intern',
                        'Graphic Designer' => 'Graphic Designer',
                    ])
                    ->searchable()
                    ->required(),

                Forms\Components\TextInput::make('base_salary') 
                    ->label('Gaji Pokok')
                    ->numeric()
                    ->prefix('Rp')
                    ->required(),

                Forms\Components\DatePicker::make('join_date') 
                    ->label('Tanggal Bergabung')
                    ->native(false),

                Forms\Components\DatePicker::make('contract_end_date') 
                    ->label('Akhir Kontrak')
                    ->native(false),

                Forms\Components\Select::make('status')
                    ->label('Status Karyawan')
                    ->options([
                        'Active' => 'Active',
                        'Inactive' => 'Inactive',
                        'Resigned' => 'Resigned',
                    ])
                    ->required()
                    ->default('Active'),

                Forms\Components\TextInput::make('document_link') 
                    ->label('Link Berkas (G-Drive / CV / Porto)')
                    ->url()
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Karyawan')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('role')
                    ->label('Posisi / Jabatan')
                    ->searchable(),

                Tables\Columns\TextColumn::make('base_salary')
                    ->label('Gaji Pokok')
                    ->money('IDR', locale: 'id') // Format Rupiah Otomatis
                    ->sortable(),

                Tables\Columns\TextColumn::make('contract_end_date')
                    ->label('Akhir Kontrak')
                    ->date('d M Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->colors([
                        'success' => 'Active',
                        'danger' => 'Inactive',
                        'warning' => 'Resigned',
                    ]),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEmployees::route('/'),
            'create' => Pages\CreateEmployee::route('/create'),
            'edit' => Pages\EditEmployee::route('/{record}/edit'),
        ];
    }
}