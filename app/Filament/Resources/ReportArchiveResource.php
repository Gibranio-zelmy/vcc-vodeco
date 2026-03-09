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
        return $form->schema([]); 
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
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat Pada')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                // TOMBOL DOWNLOAD ULANG DI SETIAP BARIS
                Tables\Actions\Action::make('download')
                    ->label('Download')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->action(fn (ReportArchive $record) => static::generatePdf($record->month, $record->year))
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReportArchives::route('/'),
        ];
    }

    // MESIN PENCETAK PDF DIPINDAH KE SINI AGAR BISA DIPANGGIL BERKALI-KALI
    public static function generatePdf($month, $year)
    {
        $monthDate = Carbon::createFromDate($year, $month, 1)->endOfMonth();
        
        $income = Transaction::whereMonth('transaction_date', $month)->whereYear('transaction_date', $year)->where('type', 'Income')->sum('amount');
        $expense = Transaction::whereMonth('transaction_date', $month)->whereYear('transaction_date', $year)->where('type', 'Expense')->sum('amount');
        $profit = $income - $expense;
        $margin = $income > 0 ? round(($profit / $income) * 100, 1) : 0;
        
        $newClientsQuery = Client::whereMonth('join_date', $month)->whereYear('join_date', $year);
        $newClients = $newClientsQuery->count();
        $newClientsList = $newClientsQuery->get(); 
        
        $totalClients = Client::where('join_date', '<=', $monthDate)->count();
        $marketingExpense = Transaction::whereMonth('transaction_date', $month)->whereYear('transaction_date', $year)
            ->where('type', 'Expense')->where('category', 'Marketing')->sum('amount');
        $cac = $newClients > 0 ? $marketingExpense / $newClients : 0;
        
        $allTimeIncome = Transaction::where('transaction_date', '<=', $monthDate)->where('type', 'Income')->sum('amount');
        $ltv = $totalClients > 0 ? $allTimeIncome / $totalClients : 0;
        
        $pipelineValue = Asset::whereMonth('created_at', $month)->whereYear('created_at', $year)->sum('value');
        $activeProjects = Asset::whereMonth('created_at', $month)->whereYear('created_at', $year)->count();
        
        $teamSize = 5; 
        $revPerEmployee = $teamSize > 0 ? $income / $teamSize : 0;
        $runway = $expense > 0 ? round($allTimeIncome / $expense, 1) : 99; 

        $arAgingList = collect(); 
        $arTotal = 0;
        if(class_exists('\App\Models\Invoice')) {
            $arAgingList = Invoice::with('client')->where('status', '!=', 'Paid')->where('issue_date', '<=', $monthDate)->get();
            $arTotal = $arAgingList->sum('amount');
        }
        
        $pdf = Pdf::loadView('pdf.report', [
            'month' => $month, 'year' => $year, 'income' => $income, 'expense' => $expense, 'profit' => $profit,
            'margin' => $margin, 'newClients' => $newClients, 'newClientsList' => $newClientsList, 'cac' => $cac,
            'ltv' => $ltv, 'pipelineValue' => $pipelineValue, 'activeProjects' => $activeProjects,
            'revPerEmployee' => $revPerEmployee, 'runway' => $runway, 'arAgingList' => $arAgingList, 'arTotal' => $arTotal
        ]);
        
        return response()->streamDownload(fn () => print($pdf->output()), "Rekap_Vodeco_{$year}_{$month}.pdf");
    }
}