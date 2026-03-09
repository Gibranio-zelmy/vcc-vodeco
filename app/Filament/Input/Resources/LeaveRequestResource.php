<?php

namespace App\Filament\Input\Resources;

use App\Filament\Input\Resources\LeaveRequestResource\Pages;
use App\Models\LeaveRequest;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class LeaveRequestResource extends Resource
{
    protected static ?string $model = LeaveRequest::class;
    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';
    protected static ?string $navigationGroup = 'HRD & MANPOWER';
    protected static ?string $navigationLabel = 'Pengajuan Cuti';
    protected static ?string $pluralModelLabel = 'Daftar Pengajuan Cuti';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Kolom user_id diisi otomatis oleh mesin, kuli tidak bisa pilih nama orang lain
                Forms\Components\Hidden::make('user_id')
                    ->default(auth()->id()),

                Forms\Components\DatePicker::make('start_date')
                    ->label('Tanggal Mulai Cuti')
                    ->required()
                    ->native(false),

                Forms\Components\DatePicker::make('end_date')
                    ->label('Tanggal Selesai')
                    ->required()
                    ->native(false)
                    ->afterOrEqual('start_date'),

                Forms\Components\Textarea::make('reason')
                    ->label('Alasan Cuti')
                    ->required()
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Nama Karyawan')
                    ->searchable()
                    // Sembunyikan kolom nama jika yang login adalah kuli biasa (karena isinya pasti nama dia sendiri)
                    ->hidden(fn () => auth()->user()->role === 'karyawan'),

                Tables\Columns\TextColumn::make('start_date')
                    ->label('Mulai')
                    ->date('d M Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('end_date')
                    ->label('Selesai')
                    ->date('d M Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->colors([
                        'warning' => 'pending_hrd',
                        'info' => 'pending_owner',
                        'success' => 'approved',
                        'danger' => 'rejected',
                    ])
                    ->formatStateUsing(fn (string $state): string => strtoupper(str_replace('_', ' ', $state))),
            ])
            ->filters([
                //
            ])
            ->actions([
                // TOMBOL VERIFIKASI HRD (Hanya muncul untuk HRD jika status masih pending_hrd)
                Tables\Actions\Action::make('verify_hrd')
                    ->label('Verifikasi HRD')
                    ->icon('heroicon-o-check-circle')
                    ->color('info')
                    ->visible(fn ($record) => auth()->user()->role === 'hrd' && $record->status === 'pending_hrd')
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $record->update(['status' => 'pending_owner']);
                    }),
                    
                Tables\Actions\ViewAction::make(),
                // TOMBOL CETAK PDF: Hanya muncul kalau status sudah APPROVED
                Tables\Actions\Action::make('download_pdf')
                    ->label('Cetak Izin')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->visible(fn ($record) => $record->status === 'approved')
                    ->action(function ($record) {
                        // Memanggil mesin PDF yang sudah ada di sistem
                        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.leave_request', ['record' => $record]);
                        
                        // Eksekusi mutlak: Download otomatis
                        return response()->streamDownload(
                            fn () => print($pdf->output()),
                            'Surat_Izin_Cuti_' . str_replace(' ', '_', $record->user->name) . '.pdf'
                        );
                    }),
            ]);
    }

    // KUNCI MUTLAK: Kuli hanya melihat cutinya sendiri, HRD melihat semuanya
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        if (auth()->user()->role === 'karyawan' || auth()->user()->role === 'operator') {
            return $query->where('user_id', auth()->id());
        }

        // Jika HRD, tampilkan semua antrean
        return $query;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLeaveRequests::route('/'),
            'create' => Pages\CreateLeaveRequest::route('/create'),
        ];
    }
}