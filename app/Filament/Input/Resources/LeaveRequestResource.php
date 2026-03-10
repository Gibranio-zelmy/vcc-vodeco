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
                    ->afterOrEqual('start_date')
                    // MESIN HAKIM MUTLAK: SOP VODECO + RADAR BENTROK
                    ->rule(function (\Filament\Forms\Get $get, ?\Illuminate\Database\Eloquent\Model $record) {
                        return function (string $attribute, $value, \Closure $fail) use ($get, $record) {
                            $startDate = $get('start_date');
                            $endDate = $value;
                            $user = auth()->user();

                            if (!$startDate || !$endDate) return;

                            $start = \Carbon\Carbon::parse($startDate);
                            $end = \Carbon\Carbon::parse($endDate);

                            // ==========================================
                            // 1. SOP MUTLAK: MAKSIMAL 1 HARI (TIDAK BISA DIRAPEL)
                            // ==========================================
                            if ($start->diffInDays($end) > 0) {
                                $fail('SOP Vodeco: Cuti tidak bisa dirapel. Anda hanya boleh mengambil 1 hari (Tanggal Mulai dan Selesai harus sama).');
                                return;
                            }

                            // ==========================================
                            // 2. SOP MUTLAK: JATAH 1 HARI PER BULAN
                            // ==========================================
                            $queryJatahBulanIni = \App\Models\LeaveRequest::where('user_id', $user->id)
                                ->whereMonth('start_date', $start->month)
                                ->whereYear('start_date', $start->year)
                                ->whereIn('status', ['pending_hrd', 'pending_owner', 'approved']);
                            
                            // Jika sedang mode Edit, abaikan data cuti yang sedang diedit ini
                            if ($record) {
                                $queryJatahBulanIni->where('id', '!=', $record->id);
                            }

                            if ($queryJatahBulanIni->exists()) {
                                $fail('SOP Vodeco: Jatah cuti untuk bulan ' . $start->translatedFormat('F Y') . ' sudah Anda gunakan. Jatah mutlak hanya 1 hari per bulan.');
                                return;
                            }

                            // ==========================================
                            // 3. RADAR ANTI-BENTROK JABATAN
                            // ==========================================
                            $employee = $user->employee;
                            if ($employee) {
                                $jabatan = $employee->role;
                                
                                $queryBentrok = \App\Models\LeaveRequest::whereHas('user.employee', function($q) use ($jabatan) {
                                        $q->where('role', $jabatan);
                                    })
                                    ->where('user_id', '!=', $user->id)
                                    ->whereIn('status', ['pending_hrd', 'pending_owner', 'approved'])
                                    ->where(function ($query) use ($startDate, $endDate) {
                                        // Rumus irisan tanggal
                                        $query->whereBetween('start_date', [$startDate, $endDate])
                                              ->orWhereBetween('end_date', [$startDate, $endDate])
                                              ->orWhere(function ($q) use ($startDate, $endDate) {
                                                  $q->where('start_date', '<=', $startDate)
                                                    ->where('end_date', '>=', $endDate);
                                              });
                                    });

                                // Jika sedang mode Edit, abaikan ID sendiri dari pengecekan
                                if ($record) {
                                    $queryBentrok->where('id', '!=', $record->id);
                                }

                                if ($queryBentrok->exists()) {
                                    $fail("Sistem Menolak: Ada rekan dengan posisi yang sama [{$jabatan}] sedang libur di tanggal tersebut. Posisi tidak boleh kosong!");
                                }
                            }
                        };
                    }),

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
                    ->hidden(fn () => auth()->user()->role === 'karyawan' || auth()->user()->role === 'operator'),

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
                Tables\Actions\Action::make('verify_hrd')
                    ->label('Verifikasi HRD')
                    ->icon('heroicon-o-check-circle')
                    ->color('info')
                    ->visible(fn ($record) => auth()->user()->role === 'hrd' && $record->status === 'pending_hrd')
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        // 1. Update status cuti jadi pending_owner
                        $record->update(['status' => 'pending_owner']);

                        // 2. TEMBAKAN INJEKSI MUTLAK KE VIP BOS
                        $admins = \App\Models\User::where('role', 'admin')->get();
                        
                        foreach ($admins as $bos) {
                            \Illuminate\Support\Facades\DB::table('notifications')->insert([
                                'id' => (string) \Illuminate\Support\Str::uuid(),
                                'type' => 'Filament\Notifications\DatabaseNotification',
                                'notifiable_type' => 'App\Models\User',
                                'notifiable_id' => $bos->id,
                                'data' => json_encode([
                                    'format' => 'filament',
                                    'title' => '⏳ Cuti Menunggu ACC Bos!',
                                    'body' => 'HRD telah memverifikasi cuti: ' . $record->user->name . '. Menunggu eksekusi Anda.',
                                    'color' => 'warning',
                                    'icon' => 'heroicon-o-shield-check',
                                ]),
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);
                        }

                        // 3. Pop-up untuk layar HRD (Dibuat Persistent / Menempel Terus)
                        \Filament\Notifications\Notification::make()
                            ->title('Berhasil Diteruskan!')
                            ->body('Data cuti telah dikirim ke Lonceng VIP Bos.')
                            ->success()
                            ->persistent() // <--- KUNCI MUTLAK
                            ->send();
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