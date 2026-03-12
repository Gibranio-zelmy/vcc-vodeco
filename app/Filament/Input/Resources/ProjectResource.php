<?php

namespace App\Filament\Input\Resources;

use App\Filament\Input\Resources\ProjectResource\Pages;
use App\Models\Project;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;

class ProjectResource extends Resource
{
    protected static ?string $model = Project::class;
    
    public static function canViewAny(): bool
    {
        return auth()->user()->role === 'operator';
    }

    protected static ?string $navigationIcon = 'heroicon-o-briefcase'; // Ikon Tas Kerja
    protected static ?string $navigationLabel = 'Input Proyek Baru';
    protected static ?string $pluralModelLabel = 'Input Proyek';
    protected static ?string $slug = 'input-proyek';
    protected static ?string $navigationGroup = 'FASE 1: KLIEN & PESANAN';
    protected static ?int $navigationSort = 1;

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
                    
                Forms\Components\Select::make('type')
                    ->label('Tipe Proyek')
                    ->options([
                        'Website' => 'Website (Dev / Domain / Hosting)',
                        'Ads' => 'Digital Ads (Meta / Google)',
                        'Design' => 'Creative Design (Logo / UI / Compro)',
                        'SEO' => 'SEO Specialist',
                        'Maintenance' => 'Maintenance / Server',
                        'Lain-lain' => 'Lain-lain',
                    ])
                    ->searchable()
                    ->required(),
                    
                Forms\Components\Select::make('status')
                    ->label('Status Awal')
                    ->options([
                        'Queue' => 'Antrean (Queue)',
                        'In Progress' => 'Sedang Dikerjakan (In Progress)',
                    ])
                    ->default('Queue')
                    ->required(),
                
                Forms\Components\TextInput::make('project_value')
                    ->label('Nilai Proyek (Estimasi/Deal)')
                    ->prefix('Rp')
                    ->numeric()
                    ->mask(\Filament\Support\RawJs::make('$money($input, \',\', \'.\', 0)'))
                    ->stripCharacters('.') 
                    ->minValue(0)
                    ->required(),
                
                Forms\Components\DatePicker::make('start_date')
                    ->label('Tanggal Mulai')
                    ->required(),
                    
                Forms\Components\DatePicker::make('deadline')
                    ->label('Tenggat Waktu (Deadline)')
                    ->required()
                    ->afterOrEqual('start_date')
                    ->validationMessages([
                        'after_or_equal' => 'Kesalahan! Deadline tidak boleh lebih cepat dari Tanggal Mulai.',
                    ]),
                    
                Forms\Components\Repeater::make('employeeAllocations')
                    ->relationship('employeeAllocations') 
                    ->label('Tim Proyek & Alokasi')
                    ->schema([
                        Forms\Components\Select::make('employee_id')
                            ->label('Pilih Karyawan')
                            ->options(\App\Models\Employee::pluck('name', 'id'))
                            ->searchable()
                            ->required(),
                                
                        Forms\Components\TextInput::make('allocation_percentage')
                            ->label('Alokasi Waktu (%)')
                            ->numeric()
                            ->default(100)
                            ->required(),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
            ]);
    }

    // FUNGSI TABLE DIHAPUS MUTLAK

    public static function getPages(): array
    {
        return [
            // PENGUNCIAN MUTLAK: Arahkan langsung ke form Create
            'index' => Pages\CreateProject::route('/'),
        ];
    }
}