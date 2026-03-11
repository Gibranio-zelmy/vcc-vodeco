<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LeaveRequestResource\Pages;
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

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days'; // Icon saya perbarui agar lebih relevan dengan kalender cuti
    protected static ?string $navigationGroup = 'OPERATIONS';
    protected static ?string $navigationLabel = 'Approval Cuti';
    protected static ?int $navigationSort = 6;

    public static function form(Form $form): Form
    {
        return $form->schema([
            // Form kosong karena VIP Bos hanya bertugas menyetujui, bukan menginput
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')->label('Karyawan'),
                Tables\Columns\TextColumn::make('start_date')->date('d M Y')->label('Mulai'),
                Tables\Columns\TextColumn::make('end_date')->date('d M Y')->label('Selesai'),
                Tables\Columns\TextColumn::make('reason')->label('Alasan')->limit(30),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->colors([
                        'warning' => 'pending_hrd',
                        'info' => 'pending_owner',
                        'success' => 'approved',
                        'danger' => 'rejected',
                    ]),
            ])
            // Filter mutlak: Bos pasti cuma mau lihat yang butuh di-acc saja
            ->modifyQueryUsing(fn (Builder $query) => $query->orderByRaw("CASE WHEN status = 'pending_owner' THEN 1 ELSE 0 END DESC"))
            ->actions([
                // PALU GODAM: APPROVE
                Tables\Actions\Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->visible(fn ($record) => $record->status === 'pending_owner')
                    ->requiresConfirmation()
                    ->action(fn ($record) => $record->update(['status' => 'approved'])),

                // PALU GODAM: REJECT (Tolak dengan Alasan)
                Tables\Actions\Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->visible(fn ($record) => $record->status === 'pending_owner')
                    ->form([
                        Forms\Components\Textarea::make('rejection_reason')
                            ->label('Alasan Penolakan')
                            ->required(),
                    ])
                    ->action(function (array $data, $record): void {
                        $record->update([
                            'status' => 'rejected',
                            'rejection_reason' => $data['rejection_reason'],
                        ]);
                    }),

                // SUNTIKAN MUTLAK: MESIN CETAK PDF
                Tables\Actions\Action::make('download_pdf')
                    ->label('Cetak Izin')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->visible(fn ($record) => $record->status === 'approved')
                    ->action(function ($record) {
                        // Memanggil mesin PDF yang sudah terpasang di VCC
                        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.leave_request', ['record' => $record]);
                        
                        // Eksekusi Download Otomatis
                        return response()->streamDownload(
                            fn () => print($pdf->output()),
                            'Surat_Izin_Cuti_' . str_replace(' ', '_', $record->user->name) . '.pdf'
                        );
                    }),
            ]);
    }

    // GEMBOK VIP: Cuma Bos (Admin) yang boleh buka kotak surat ini
    public static function canViewAny(): bool
    {
        return auth()->user()->role === 'admin';
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLeaveRequests::route('/'),
        ];
    }
}