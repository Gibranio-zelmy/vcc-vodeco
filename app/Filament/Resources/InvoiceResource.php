<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InvoiceResource\Pages;
use App\Models\Invoice;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class InvoiceResource extends Resource
{
    protected static ?string $model = Invoice::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'Invoices / Tagihan';
    protected static ?string $navigationGroup = 'CASHFLOW';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('client_id')
                    ->label('Klien')
                    ->relationship('client', 'name')
                    ->searchable()
                    ->required(),
                    
                Forms\Components\TextInput::make('invoice_number')
                    ->label('Nomor Invoice')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),
                    
                Forms\Components\TextInput::make('amount')
                    ->label('Total Tagihan')
                    ->prefix('Rp')
                    ->numeric()
                    ->mask(\Filament\Support\RawJs::make('$money($input, \',\', \'.\', 0)'))
                    ->stripCharacters('.')
                    ->minValue(0)
                    ->required(),
                    
                Forms\Components\DatePicker::make('issue_date')
                    ->label('Tanggal Terbit')
                    ->default(now())
                    ->required(),
                    
                Forms\Components\DatePicker::make('due_date')
                    ->label('Jatuh Tempo')
                    ->required(),
                    
                Forms\Components\Select::make('status')
                    ->label('Status')
                    ->options([
                        'Unpaid' => 'Belum Dibayar (Unpaid)',
                        'Partial' => 'Dicicil / DP (Partial)',
                        'Paid' => 'Lunas (Paid)',
                    ])
                    ->default('Unpaid')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('invoice_number')
                    ->label('No. Invoice')
                    ->searchable(),
                    
                Tables\Columns\TextColumn::make('client.name')
                    ->label('Nama Klien')
                    ->searchable()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('amount')
                    ->label('Total Tagihan')
                    ->formatStateUsing(fn ($state) => 'Rp ' . number_format((float)$state, 0, ',', '.'))
                    ->alignRight()
                    ->sortable(),
                    
                // TAMBAHAN MUTLAK: Telah Dibayar
                Tables\Columns\TextColumn::make('paid_amount')
                    ->label('Telah Dibayar')
                    ->formatStateUsing(fn ($state) => 'Rp ' . number_format((float)$state, 0, ',', '.'))
                    ->alignRight()
                    ->color('success'),
                    
                // TAMBAHAN MUTLAK: Sisa Tagihan (Radar AR)
                Tables\Columns\TextColumn::make('sisa')
                    ->label('Sisa Tagihan')
                    ->state(fn (Invoice $record): float => (float)$record->amount - (float)$record->paid_amount)
                    ->formatStateUsing(fn ($state) => 'Rp ' . number_format((float)$state, 0, ',', '.'))
                    ->alignRight()
                    ->color('danger')
                    ->weight('bold'),
                    
                Tables\Columns\TextColumn::make('due_date')
                    ->label('Jatuh Tempo')
                    ->date('d M Y')
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Unpaid' => 'danger',
                        'Partial' => 'warning',
                        'Paid' => 'success',
                        default => 'secondary',
                    }),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                //
            ])
            ->actions([
                // TOMBOL PEMBAYARAN AJAIB (Disinkronkan dengan Radar Kuli)
                Tables\Actions\Action::make('catat_pembayaran')
                    ->label('Catat Bayar / DP')
                    ->icon('heroicon-o-banknotes')
                    ->color('success')
                    ->visible(fn (Invoice $record) => strtolower($record->status) !== 'paid') // Sembunyi jika sudah Lunas
                    ->form(fn (Invoice $record) => [
                        
                        // Menampilkan Sisa Tagihan di dalam form agar Bos tidak perlu menebak
                        Forms\Components\Placeholder::make('info_sisa')
                            ->label('SISA YANG HARUS DIBAYAR:')
                            ->content('Rp ' . number_format((float)($record->amount - $record->paid_amount), 0, ',', '.')),
                            
                        Forms\Components\TextInput::make('amount_paid')
                            ->label('Nominal Masuk')
                            ->prefix('Rp')
                            ->numeric()
                            ->mask(\Filament\Support\RawJs::make('$money($input, \',\', \'.\', 0)'))
                            ->stripCharacters('.')
                            ->minValue(0)
                            ->maxValue((float)($record->amount - $record->paid_amount)) // Gembok: Tidak bisa bayar lebih dari sisa
                            ->default((float)($record->amount - $record->paid_amount))
                            ->required(),
                            
                        Forms\Components\DatePicker::make('payment_date')
                            ->label('Tanggal Pembayaran')
                            ->default(now())
                            ->required(),
                            
                        Forms\Components\TextInput::make('notes')
                            ->label('Keterangan (Contoh: DP 50% atau Pelunasan)')
                            ->required(),
                    ])
                    ->action(function (Invoice $record, array $data) {
                        $nominal = (float) $data['amount_paid'];
                        
                        // 1. Catat ke brankas Transaksi otomatis
                        \App\Models\Transaction::create([
                            'type' => 'income',
                            'category' => 'project',
                            'amount' => $nominal,
                            'transaction_date' => $data['payment_date'],
                            'description' => $data['notes'] . ' | Invoice: ' . $record->invoice_number,
                            'invoice_id' => $record->id, 
                        ]);

                        // 2. Kalkulasi Mutlak
                        $totalBayarBaru = $record->paid_amount + $nominal;
                        $status_baru = ($totalBayarBaru >= $record->amount) ? 'Paid' : 'Partial';

                        // 3. Update invoice 
                        $record->update([
                            'paid_amount' => $totalBayarBaru,
                            'status' => $status_baru
                        ]);
                    })
                    ->successNotificationTitle('Pembayaran & Transaksi Berhasil Dicatat!'),
                    
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
            'index' => Pages\ListInvoices::route('/'),
            'create' => Pages\CreateInvoice::route('/create'),
            'edit' => Pages\EditInvoice::route('/{record}/edit'),
        ];
    }
}