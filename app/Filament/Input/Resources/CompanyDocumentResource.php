<?php

namespace App\Filament\Input\Resources;

use App\Filament\Input\Resources\CompanyDocumentResource\Pages;
use App\Models\CompanyDocument;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class CompanyDocumentResource extends Resource
{
    protected static ?string $model = CompanyDocument::class;
    protected static ?string $navigationIcon = 'heroicon-o-book-open';
    protected static ?string $navigationGroup = 'LEGAL & SOP';
    protected static ?string $navigationLabel = 'Pusat Dokumen';

    // ==========================================
    // GEMBOK KASTA MUTLAK (AUTHORIZATION)
    // ==========================================
    
    // 1. Semua orang boleh masuk ke halamannya
    public static function canViewAny(): bool
    {
        return true; 
    }

    // 2. HANYA HRD yang boleh nambah dokumen baru
    public static function canCreate(): bool
    {
        return auth()->user()->role === 'hrd';
    }

    // 3. HANYA HRD yang boleh ngedit (Kuli tidak bisa!)
    public static function canEdit(Model $record): bool
    {
        return auth()->user()->role === 'hrd';
    }

    // 4. TIDAK ADA YANG BOLEH MENGHAPUS DI LANTAI INI (HRD harus minta tolong VIP)
    public static function canDelete(Model $record): bool
    {
        return false; 
    }

    // 5. Matikan juga fitur hapus massal (Bulk Delete)
    public static function canDeleteAny(): bool
    {
        return false;
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
                    ->multiple()
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
                    ->required(),

                Forms\Components\FileUpload::make('file_attachment')
                    ->label('Upload File (PDF)')
                    ->acceptedFileTypes(['application/pdf'])
                    ->directory('company-documents')
                    ->maxSize(5120),

                Forms\Components\TextInput::make('drive_link')
                    ->label('Atau Gunakan Link Google Drive')
                    ->url()
                    ->placeholder('https://drive.google.com/...'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')->label('Judul Dokumen')->searchable()->weight('bold'),
                Tables\Columns\TextColumn::make('category')->label('Kategori')->badge(),
                Tables\Columns\TextColumn::make('target_roles')->label('Akses Kasta')->badge()->color('info')->separator(','),
                
                Tables\Columns\TextColumn::make('file_attachment')
                    ->label('File')
                    ->formatStateUsing(fn ($state) => $state ? 'Buka PDF' : '-')
                    ->url(fn ($record) => $record->file_attachment ? asset('storage/' . $record->file_attachment) : null)
                    ->openUrlInNewTab()
                    ->color('success'),

                Tables\Columns\TextColumn::make('drive_link')
                    ->label('Link Drive')
                    ->formatStateUsing(fn ($state) => $state ? 'Buka G-Drive' : '-')
                    ->url(fn ($record) => $record->drive_link)
                    ->openUrlInNewTab()
                    ->color('info'),
            ])
            ->actions([
                // Tombol Edit akan otomatis GAIB untuk kuli karena fungsi canEdit() di atas
                Tables\Actions\EditAction::make(),
            ]);
            // Tombol Delete kita buang total dari tabel ini
    }

    // ==========================================
    // MESIN SENSOR KASTA (FILTER BACA BUKU)
    // ==========================================
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = auth()->user();

        // HRD dan Bos kebal sensor
        if ($user->role === 'hrd' || $user->role === 'admin') {
            return $query;
        }

        // Cari tahu jabatan kuli ini
        $jabatanKuli = $user->employee ? $user->employee->role : 'TIDAK_PUNYA_JABATAN';

        // Filter mutlak
        return $query->where(function ($q) use ($jabatanKuli) {
            $q->whereJsonContains('target_roles', 'Semua Karyawan')
              ->orWhereJsonContains('target_roles', $jabatanKuli);
        });
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