<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EmployeeResource\Pages;
use App\Filament\Resources\EmployeeResource\RelationManagers;
use App\Models\Employee;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class EmployeeResource extends Resource
{
    protected static ?string $model = Employee::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'OPERATIONS';
    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nama Karyawan')
                    ->required()
                    ->maxLength(255),
                    
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
                    ->prefix('Rp')
                    ->numeric()
                    ->minValue(0)
                    ->required(),
                    
                Forms\Components\DatePicker::make('join_date')
                    ->label('Tanggal Bergabung'),
                    
                Forms\Components\DatePicker::make('contract_end_date')
                    ->label('Akhir Kontrak'),
                    
                Forms\Components\Select::make('status')
                    ->label('Status Karyawan')
                    ->options([
                        'Active' => 'Aktif (Active)',
                        'Inactive' => 'Non-Aktif (Inactive)',
                        'Resigned' => 'Keluar (Resigned)',
                    ])
                    ->default('Active')
                    ->required(),
                    
                // PERBAIKAN FORM: Form URL untuk Google Drive (CV, Biodata, Porto)
                Forms\Components\TextInput::make('document_link')
                    ->label('Link Berkas (G-Drive / CV / Porto)')
                    ->url() // Validasi mutlak: Sistem menolak jika tidak diawali http:// atau https://
                    ->placeholder('https://drive.google.com/...')
                    ->columnSpanFull(), // Dibuat memanjang penuh dari kiri ke kanan agar link panjang tidak terpotong
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama')
                    ->searchable(),
                    
                Tables\Columns\TextColumn::make('role')
                    ->label('Jabatan')
                    ->searchable(),
                    
                Tables\Columns\TextColumn::make('base_salary')
                    ->label('Gaji Pokok')
                    ->formatStateUsing(fn ($state) => 'Rp ' . number_format((float)$state, 0, ',', '.'))
                    ->alignRight()
                    ->sortable(),

                // 1. LINK DRIVE DIBUAT RAPI (Hanya tulisan "Buka Arsip")
                Tables\Columns\TextColumn::make('document_link')
                    ->label('Arsip')
                    ->icon('heroicon-o-folder-open')
                    ->color('info')
                    ->formatStateUsing(fn () => 'Buka Arsip') // Menyembunyikan URL panjang
                    ->url(fn ($record) => $record->document_link)
                    ->openUrlInNewTab(),
                    
                // 2. TANGGAL GABUNG DIHILANGKAN DARI LAYAR UTAMA (Bisa dilihat jika butuh saja)
                Tables\Columns\TextColumn::make('join_date')
                    ->label('Bergabung')
                    ->date('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true), 
                    
                // 3. RADAR SISA KONTRAK (Indikator Keputusan CEO)
                Tables\Columns\TextColumn::make('contract_end_date')
                    ->label('Status Kontrak')
                    ->formatStateUsing(function ($state) {
                        if (!$state) return 'Permanen';
                        
                        // Kunci waktu mutlak di jam 00:00 agar tidak ada pecahan desimal
                        $end = \Carbon\Carbon::parse($state)->startOfDay();
                        $today = now()->startOfDay();
                        
                        // Jika tanggal sudah lewat dari hari ini
                        if ($end->isBefore($today)) {
                            return 'Expired / Habis';
                        }
                        
                        // Hitung selisih hari sebagai angka bulat murni
                        $diff = (int) $today->diffInDays($end);
                        return $diff . ' Hari Lagi';
                    })
                    ->badge()
                    ->color(function ($state) {
                        if (!$state) return 'success';
                        
                        $end = \Carbon\Carbon::parse($state)->startOfDay();
                        $today = now()->startOfDay();
                        
                        if ($end->isBefore($today)) return 'danger';
                        
                        $diff = (int) $today->diffInDays($end);
                        if ($diff <= 30) return 'warning';
                        
                        return 'success';
                    })
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Active' => 'success',
                        'Inactive' => 'warning',
                        'Resigned' => 'danger',
                        default => 'gray',
                    })
                    ->searchable(),
            ])
            ->defaultSort('created_at', 'desc')
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
            'index' => Pages\ListEmployees::route('/'),
            'create' => Pages\CreateEmployee::route('/create'),
            'edit' => Pages\EditEmployee::route('/{record}/edit'),
        ];
    }
}