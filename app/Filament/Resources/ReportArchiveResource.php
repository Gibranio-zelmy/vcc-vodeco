<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReportArchiveResource\Pages;
use App\Models\ReportArchive;
use App\Models\Transaction;
use App\Models\Asset;
use App\Models\Client;
use App\Models\Invoice;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class ReportArchiveResource extends Resource
{
    protected static ?string $model = ReportArchive::class;
    protected static ?string $navigationIcon = 'heroicon-o-archive-box';
    protected static ?string $navigationLabel = 'Arsip Laporan';
    protected static ?string $navigationGroup = 'ANALYTICS';
    protected static ?int $navigationSort = 6;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Laporan')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->label('Judul Laporan')
                            ->placeholder('Contoh: Tutup Buku Maret 2026')
                            ->required()
                            ->columnSpanFull(),

                        Forms\Components\Select::make('month')
                            ->label('Bulan')
                            ->options([
                                '1' => 'Januari', '2' => 'Februari', '3' => 'Maret', '4' => 'April',
                                '5' => 'Mei', '6' => 'Juni', '7' => 'Juli', '8' => 'Agustus',
                                '9' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember',
                            ])
                            ->required()
                            ->live() // SAKELAR GAIB: Memicu aksi saat diubah
                            ->afterStateUpdated(fn (Set $set, Get $get) => static::calculateFinancials($set, $get)),

                        Forms\Components\Select::make('year')
                            ->label('Tahun')
                            ->options(array_combine(range(2024, 2035), range(2024, 2035))) 
                            ->default(now()->year)
                            ->required()
                            ->live() // SAKELAR GAIB: Memicu aksi saat diubah
                            ->afterStateUpdated(fn (Set $set, Get $get) => static::calculateFinancials($set, $get)),
                    ])->columns(2),

                Forms\Components\Section::make('Kalkulasi Otomatis (Mesin VCC)')
                    ->description('Angka di bawah ini ditarik otomatis dari Transaksi (Income & Expense) bulan terkait. Bos tetap bisa menyesuaikan angkanya secara manual jika diperlukan.')
                    ->schema([
                        Forms\Components\TextInput::make('total_revenue')
                            ->label('Total Pemasukan (Omzet)')
                            ->numeric()
                            ->prefix('Rp')
                            ->default(0),

                        Forms\Components\TextInput::make('total_expense')
                            ->label('Total Pengeluaran')
                            ->numeric()
                            ->prefix('Rp')
                            ->default(0),

                        Forms\Components\TextInput::make('net_profit')
                            ->label('Net Profit (Keuntungan Bersih)')
                            ->numeric()
                            ->prefix('Rp')
                            ->default(0),
                    ])->columns(3),
            ]);
    }

   // ==============================================================
    // MESIN KALKULATOR GAIB (REVISI MUTLAK: SUMBER DARI TRANSACTION INCOME/EXPENSE)
    // ==============================================================
    public static function calculateFinancials(Set $set, Get $get)
    {
        $month = $get('month');
        $year = $get('year');

        // Jika bulan atau tahun belum dipilih, hentikan proses
        if (!$month || !$year) {
            return;
        }

        // KALIBRASI MUTLAK: Pemasukan & pengeluaran disedot dari tabel Transaction (arus kas riil)
        $income = Transaction::whereMonth('transaction_date', $month)
            ->whereYear('transaction_date', $year)
            ->where('type', 'income')
            ->sum('amount');

        // Pengeluaran ditarik dari Transaksi dengan type = expense
        $expense = Transaction::whereMonth('transaction_date', $month)
            ->whereYear('transaction_date', $year)
            ->where('type', 'expense')
            ->sum('amount');

        // Kalkulasi Keuntungan Bersih
        $profit = $income - $expense;

        // #region agent log
        @file_put_contents(
            '/Users/gibraniozelmy/Documents/Vodeco-Project/.cursor/debug-07a212.log',
            json_encode([
                'sessionId' => '07a212',
                'runId' => 'pre-fix-1',
                'hypothesisId' => 'H1',
                'location' => __FILE__ . ':' . __LINE__,
                'message' => 'ReportArchiveResource::calculateFinancials summary',
                'data' => [
                    'month' => $month,
                    'year' => $year,
                    'income_from_invoices_paid' => $income,
                    'expense_from_transactions' => $expense,
                    'profit' => $profit,
                ],
                'timestamp' => round(microtime(true) * 1000),
            ]) . PHP_EOL,
            FILE_APPEND
        );
        // #endregion

        // Tembakkan angkanya ke dalam Form secara otomatis
        $set('total_revenue', $income);
        $set('total_expense', $expense);
        $set('net_profit', $profit);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Nama Laporan')
                    ->searchable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('month')
                    ->label('Bulan')
                    ->searchable(),
                Tables\Columns\TextColumn::make('year')
                    ->label('Tahun')
                    ->searchable(),
                Tables\Columns\TextColumn::make('total_revenue')
                    ->label('Omzet')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('net_profit')
                    ->label('Profit')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat Pada')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                Tables\Actions\Action::make('download')
                    ->label('Download')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->action(fn (ReportArchive $record) => static::generatePdf($record->month, $record->year)),
                
                Tables\Actions\DeleteAction::make()
                    ->label('Hapus'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReportArchives::route('/'),
            'create' => Pages\CreateReportArchive::route('/create'),
            'edit' => Pages\EditReportArchive::route('/{record}/edit'),
        ];
    }

    // ==========================================
    // MESIN PENCETAK PDF
    // ==========================================
    public static function generatePdf($month, $year)
    {
        $monthDate = Carbon::createFromDate($year, $month, 1)->endOfMonth();
        
        // Sumber pemasukan & pengeluaran dikunci dari Transaction (arus kas riil)
        $income = Transaction::whereMonth('transaction_date', $month)
            ->whereYear('transaction_date', $year)
            ->where('type', 'income')
            ->sum('amount');

        $expense = Transaction::whereMonth('transaction_date', $month)
            ->whereYear('transaction_date', $year)
            ->where('type', 'expense')
            ->sum('amount');
        $profit = $income - $expense;
        $margin = $income > 0 ? round(($profit / $income) * 100, 1) : 0;
        
        $newClientsQuery = Client::whereMonth('join_date', $month)->whereYear('join_date', $year);
        $newClients = $newClientsQuery->count();
        $newClientsList = $newClientsQuery->get(); 
        
        $totalClients = Client::where('join_date', '<=', $monthDate)->count();
        $marketingExpense = Transaction::whereMonth('transaction_date', $month)->whereYear('transaction_date', $year)
            ->where('type', 'expense')->where('category', 'Marketing')->sum('amount');
        $cac = $newClients > 0 ? $marketingExpense / $newClients : 0;
        
        $allTimeIncome = Transaction::where('transaction_date', '<=', $monthDate)->where('type', 'income')->sum('amount');
        $ltv = $totalClients > 0 ? $allTimeIncome / $totalClients : 0;
        
        $pipelineValue = Asset::whereMonth('created_at', $month)->whereYear('created_at', $year)->sum('value');
        $activeProjects = Asset::whereMonth('created_at', $month)->whereYear('created_at', $year)->count();
        
        $teamSize = 5; 
        $revPerEmployee = $teamSize > 0 ? $income / $teamSize : 0;
        $runway = $expense > 0 ? round($allTimeIncome / $expense, 1) : 99; 

        $arAgingList = collect(); 
        $arTotal = 0;
        if(class_exists('\App\Models\Invoice')) {
            // AR Aging: hanya invoice yang belum lunas, dihitung sisanya (amount - paid_amount)
            $arAgingList = Invoice::with('client')
                ->whereIn('status', ['Unpaid', 'Partial', 'Overdue'])
                ->where('issue_date', '<=', $monthDate)
                ->get();

            $arTotal = $arAgingList->sum(function (Invoice $invoice) {
                return max(0, (float)$invoice->amount - (float)$invoice->paid_amount);
            });
        }

        // #region agent log
        @file_put_contents(
            '/Users/gibraniozelmy/Documents/Vodeco-Project/.cursor/debug-07a212.log',
            json_encode([
                'sessionId' => '07a212',
                'runId' => 'pre-fix-1',
                'hypothesisId' => 'H2',
                'location' => __FILE__ . ':' . __LINE__,
                'message' => 'ReportArchiveResource::generatePdf metrics',
                'data' => [
                    'month' => $month,
                    'year' => $year,
                    'income_from_transactions' => $income,
                    'expense_from_transactions' => $expense,
                    'profit' => $profit,
                    'margin' => $margin,
                    'newClients' => $newClients,
                    'totalClients' => $totalClients,
                    'marketingExpense' => $marketingExpense,
                    'cac' => $cac,
                    'allTimeIncome' => $allTimeIncome,
                    'ltv' => $ltv,
                    'pipelineValue' => $pipelineValue,
                    'activeProjects' => $activeProjects,
                    'teamSize' => $teamSize,
                    'revPerEmployee' => $revPerEmployee,
                    'runway' => $runway,
                    'arTotal' => $arTotal,
                ],
                'timestamp' => round(microtime(true) * 1000),
            ]) . PHP_EOL,
            FILE_APPEND
        );
        // #endregion
        
        $pdf = Pdf::loadView('pdf.report', [
            'month' => $month, 'year' => $year, 'income' => $income, 'expense' => $expense, 'profit' => $profit,
            'margin' => $margin, 'newClients' => $newClients, 'newClientsList' => $newClientsList, 'cac' => $cac,
            'ltv' => $ltv, 'pipelineValue' => $pipelineValue, 'activeProjects' => $activeProjects,
            'revPerEmployee' => $revPerEmployee, 'runway' => $runway, 'arAgingList' => $arAgingList, 'arTotal' => $arTotal
        ]);
        
        return response()->streamDownload(fn () => print($pdf->output()), "Rekap_Vodeco_{$year}_{$month}.pdf");
    }
}