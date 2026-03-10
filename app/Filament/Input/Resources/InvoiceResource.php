<?php

namespace App\Filament\Input\Resources;

use App\Filament\Input\Resources\InvoiceResource\Pages;
use App\Models\Invoice;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class InvoiceResource extends Resource
{
    protected static ?string $model = Invoice::class;
    public static function canViewAny(): bool
    {
        return auth()->user()->role === 'operator';
    }
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'Meja Kasir (Invoice)';
    protected static ?string $pluralModelLabel = 'Meja Kasir (Invoice)';
    protected static ?string $slug = 'input-invoice';
    protected static ?string $navigationGroup = '2. FASE FINANCE';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('client_id')
                    ->label('Nama Klien')
                    ->relationship('client', 'name')
                    ->searchable()
                    ->required(),
                    
                Forms\Components\TextInput::make('invoice_number')
                    ->label('Nomor Invoice')
                    ->required()
                    ->unique(ignoreRecord: true),
                    
                Forms\Components\DatePicker::make('issue_date')
                    ->label('Tanggal Terbit')
                    ->default(now())
                    ->required(),
                    
                Forms\Components\DatePicker::make('due_date')
                    ->label('Jatuh Tempo')
                    ->required(),
                    
                Forms\Components\TextInput::make('amount')
                    ->label('Total Tagihan (IDR)')
                    ->prefix('Rp')
                    ->numeric()
                    ->mask(\Filament\Support\RawJs::make('$money($input, \',\', \'.\', 0)'))
                    ->stripCharacters('.')
                    ->minValue(0)
                    ->required(),
                    
                Forms\Components\Select::make('status')
                    ->label('Status Tagihan')
                    ->options([
                        'Unpaid' => 'Belum Dibayar (Unpaid)',
                    ])
                    ->default('Unpaid')
                    ->disabled() 
                    ->dehydrated() 
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(
                // Hanya tampilkan yang belum lunas
                Invoice::query()->whereIn('status', ['Unpaid', 'Partial'])
            )
            ->columns([
                Tables\Columns\TextColumn::make('invoice_number')->label('No. Invoice')->searchable(),
                Tables\Columns\TextColumn::make('client.name')->label('Klien')->searchable(),
                
                Tables\Columns\TextColumn::make('amount')
                    ->label('Total Tagihan')
                    ->formatStateUsing(fn ($state) => 'Rp ' . number_format((float)$state, 0, ',', '.'))
                    ->weight('bold'),
                    
                // MENAMPILKAN UANG YANG SUDAH MASUK (DP)
                Tables\Columns\TextColumn::make('paid_amount')
                    ->label('Telah Dibayar')
                    ->formatStateUsing(fn ($state) => 'Rp ' . number_format((float)$state, 0, ',', '.'))
                    ->color('success'),
                    
                // MENGHITUNG OTOMATIS SISA TAGIHAN
                Tables\Columns\TextColumn::make('sisa')
                    ->label('Sisa Tagihan')
                    ->state(fn ($record) => $record->amount - $record->paid_amount)
                    ->formatStateUsing(fn ($state) => 'Rp ' . number_format((float)$state, 0, ',', '.'))
                    ->color('danger')
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->colors([
                        'danger' => 'Unpaid',
                        'warning' => 'Partial',
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('terima_pembayaran')
                    ->label('Terima Uang')
                    ->icon('heroicon-o-banknotes')
                    ->color('success')
                    ->form(fn (Invoice $record) => [
                        
                        // Menampilkan informasi sisa tagihan di dalam pop-up form
                        Forms\Components\Placeholder::make('info_sisa')
                            ->label('SISA YANG HARUS DIBAYAR:')
                            ->content('Rp ' . number_format((float)($record->amount - $record->paid_amount), 0, ',', '.')),

                        Forms\Components\TextInput::make('nominal_masuk')
                            ->label('Nominal Transfer Masuk')
                            ->prefix('Rp')
                            ->numeric()
                            ->mask(\Filament\Support\RawJs::make('$money($input, \',\', \'.\', 0)'))
                            ->stripCharacters('.')
                            ->default($record->amount - $record->paid_amount) 
                            ->maxValue($record->amount - $record->paid_amount) 
                            ->required(),
                    ])
                    ->action(function (Invoice $record, array $data) {
                        $nominal = (float) $data['nominal_masuk'];
                        $sisa_sebelumnya = $record->amount - $record->paid_amount;
                        $total_bayar_baru = $record->paid_amount + $nominal;

                        // OTOMATISASI MUTLAK: Mesin yang menentukan status, bukan kuli.
                        $status_baru = ($total_bayar_baru >= $record->amount) ? 'Paid' : 'Partial';

                        // 1. Lempar uang otomatis ke Radar Income VIP
                        \App\Models\Transaction::create([
                            'transaction_date' => now(),
                            'type' => 'income',
                            'category' => 'project',
                            'amount' => $nominal,
                            'description' => ($status_baru === 'Paid' && $sisa_sebelumnya == $record->amount ? 'Pelunasan Langsung ' : ($status_baru === 'Paid' ? 'Pelunasan Sisa ' : 'DP ')) . 'Invoice: ' . $record->invoice_number,
                        ]);

                        // 2. Update invoice
                        $record->update([
                            'paid_amount' => $total_bayar_baru,
                            'status' => $status_baru
                        ]);

                        // ==========================================
                        // 3. SUNTIKAN LONCENG BYPASS: UANG MASUK!
                        // ==========================================
                        $rupiah_masuk = 'Rp ' . number_format($nominal, 0, ',', '.');
                        $admins = \App\Models\User::whereIn('role', ['admin', 'Admin'])->get();

                        foreach ($admins as $bos) {
                            \Illuminate\Support\Facades\DB::table('notifications')->insert([
                                'id' => (string) \Illuminate\Support\Str::uuid(),
                                'type' => 'Filament\Notifications\DatabaseNotification',
                                'notifiable_type' => 'App\Models\User',
                                'notifiable_id' => $bos->id,
                                'data' => json_encode([
                                    'format' => 'filament',
                                    'title' => '💰 UANG MASUK: ' . $rupiah_masuk,
                                    'body' => ($status_baru === 'Paid' ? 'LUNAS MUTLAK! ' : 'DP MASUK! ') . 'Pembayaran untuk Invoice ' . $record->invoice_number . ' telah diterima dan masuk ke Brankas VIP.',
                                    'color' => 'success',
                                    'icon' => 'heroicon-o-banknotes',
                                ]),
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);
                        }

                        // 4. Notifikasi Cerdas untuk Kuli
                        \Filament\Notifications\Notification::make()
                            ->title($status_baru === 'Paid' ? 'LUNAS MUTLAK!' : 'DP TERCATAT!')
                            ->body($status_baru === 'Paid' ? 'Tagihan selesai. Income terlempar ke VIP.' : 'Sisa tagihan telah di-update otomatis.')
                            ->success()
                            ->send();
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInvoices::route('/'),
            'create' => Pages\CreateInvoice::route('/create'),
        ];
    }
}