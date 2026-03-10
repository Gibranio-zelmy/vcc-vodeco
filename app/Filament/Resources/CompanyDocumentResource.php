<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CompanyDocumentResource\Pages;
use App\Models\CompanyDocument;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CompanyDocumentResource extends Resource
{
    protected static ?string $model = CompanyDocument::class;
    protected static ?string $navigationIcon = 'heroicon-o-book-open';
    protected static ?string $navigationGroup = 'LEGAL & SOP';
    protected static ?string $navigationLabel = 'Pusat Dokumen (VIP)';

    // GEMBOK MUTLAK: Hanya Admin (Bos)
    public static function canViewAny(): bool
    {
        return auth()->user()->role === 'admin';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->label('Judul Dokumen')
                    ->required()
                    ->maxLength(255),

                Forms\Components\Select::make('category')
                    ->label('Kategori')
                    ->options([
                        'SOP' => 'Standard Operating Procedure (SOP)',
                        'Jobdesk' => 'Deskripsi Pekerjaan (Jobdesk)',
                        'Peraturan' => 'Peraturan Perusahaan',
                        'Tata Tertib' => 'Tata Tertib',
                        'Lainnya' => 'Lain-lain',
                    ])
                    ->required(),

                Forms\Components\Select::make('target_roles')
                    ->label('Target Pembaca (Kasta)')
                    ->multiple() // Bisa pilih lebih dari satu
                    ->options([
                        'Semua Karyawan' => 'Semua Karyawan (Global)',
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
                    ->required()
                    ->helperText('Pilih "Semua Karyawan" jika aturan ini berlaku umum. Atau pilih posisi tertentu agar yang lain tidak bisa melihatnya.'),

                Forms\Components\FileUpload::make('file_attachment')
                    ->label('Upload File (PDF)')
                    ->acceptedFileTypes(['application/pdf'])
                    ->directory('company-documents')
                    ->maxSize(5120), // Maksimal 5MB

                Forms\Components\TextInput::make('drive_link')
                    ->label('Atau Gunakan Link Google Drive')
                    ->url()
                    ->placeholder('https://drive.google.com/...')
                    ->helperText('Gunakan ini jika file terlalu besar untuk di-upload langsung.'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Judul Dokumen')
                    ->searchable()
                    ->weight('bold'),
                
                Tables\Columns\TextColumn::make('category')
                    ->label('Kategori')
                    ->badge(),

                Tables\Columns\TextColumn::make('target_roles')
                    ->label('Akses Kasta')
                    ->badge()
                    ->color('info')
                    ->separator(','),

                // Tombol Buka PDF Langsung
                Tables\Columns\TextColumn::make('file_attachment')
                    ->label('File')
                    ->formatStateUsing(fn ($state) => $state ? 'Buka PDF' : '-')
                    ->url(fn ($record) => $record->file_attachment ? asset('storage/' . $record->file_attachment) : null)
                    ->openUrlInNewTab()
                    ->color('success')
                    ->icon(fn ($state) => $state ? 'heroicon-o-document-text' : ''),

                // Tombol Buka G-Drive Langsung
                Tables\Columns\TextColumn::make('drive_link')
                    ->label('Link Drive')
                    ->formatStateUsing(fn ($state) => $state ? 'Buka G-Drive' : '-')
                    ->url(fn ($record) => $record->drive_link)
                    ->openUrlInNewTab()
                    ->color('info')
                    ->icon(fn ($state) => $state ? 'heroicon-o-link' : ''),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCompanyDocuments::route('/'),
            'create' => Pages\CreateCompanyDocument::route('/create'),
            'edit' => Pages\EditCompanyDocument::route('/{record}/edit'),
        ];
    }
}