<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Transaction;
use App\Models\Asset;
use App\Models\Client;
use App\Models\Invoice;
use Carbon\Carbon;

class MonthlyReport extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-chart-bar';
    protected static ?string $navigationLabel = 'Laporan Bulanan';
    protected static ?string $title = 'Arsip & Rekap Histori Vodeco';
    protected static ?string $navigationGroup = 'Evaluasi';
    protected static string $view = 'filament.pages.monthly-report';

    protected function getHeaderActions(): array
    {
        return [
            Action::make('cetak_laporan')
                ->label('Cetak PDF Laporan')
                ->icon('heroicon-o-printer')
                ->color('primary')
                ->form([
                    Select::make('month')
                        ->label('Bulan')
                        ->options([
                            '01' => 'Januari', '02' => 'Februari', '03' => 'Maret',
                            '04' => 'April', '05' => 'Mei', '06' => 'Juni',
                            '07' => 'Juli', '08' => 'Agustus', '09' => 'September',
                            '10' => 'Oktober', '11' => 'November', '12' => 'Desember',
                        ])
                        ->default(now()->format('m'))
                        ->required(),
                    Select::make('year')
                        ->label('Tahun')
                        ->options(array_combine(range(2022, now()->year), range(2022, now()->year)))
                        ->default(now()->year)
                        ->required(),
                ])
                ->action(function (array $data) {
                    $monthDate = Carbon::createFromDate($data['year'], $data['month'], 1)->endOfMonth();
                
                    // 1. Metrik Finansial Dasar (SEKARANG MEMBACA transaction_date)
                    $income = Transaction::whereMonth('transaction_date', $data['month'])->whereYear('transaction_date', $data['year'])->where('type', 'Income')->sum('amount');
                    $expense = Transaction::whereMonth('transaction_date', $data['month'])->whereYear('transaction_date', $data['year'])->where('type', 'Expense')->sum('amount');
                    $profit = $income - $expense;
                    $margin = $income > 0 ? round(($profit / $income) * 100, 1) : 0;
                
                    // 2. Metrik Klien, CAC & LTV
                    $newClientsQuery = Client::whereMonth('join_date', $data['month'])->whereYear('join_date', $data['year']);
                    $newClients = $newClientsQuery->count();
                    $newClientsList = $newClientsQuery->get(); 
                    
                    $totalClients = Client::where('join_date', '<=', $monthDate)->count();
                    
                    // Biaya Marketing juga membaca transaction_date
                    $marketingExpense = Transaction::whereMonth('transaction_date', $data['month'])->whereYear('transaction_date', $data['year'])
                        ->where('type', 'Expense')->where('category', 'Marketing')->sum('amount');
                    $cac = $newClients > 0 ? $marketingExpense / $newClients : 0;
                
                    $allTimeIncome = Transaction::where('transaction_date', '<=', $monthDate)->where('type', 'Income')->sum('amount');
                    $ltv = $totalClients > 0 ? $allTimeIncome / $totalClients : 0;
                
                    // 3. Metrik Operasional & Pipeline (Asset tetap pakai created_at / start_date)
                    $pipelineValue = Asset::whereMonth('created_at', $data['month'])->whereYear('created_at', $data['year'])->sum('value');
                    $activeProjects = Asset::whereMonth('created_at', $data['month'])->whereYear('created_at', $data['year'])->count();
                    
                    $teamSize = 5; 
                    $revPerEmployee = $teamSize > 0 ? $income / $teamSize : 0;
                    $runway = $expense > 0 ? round($allTimeIncome / $expense, 1) : 99; 

                    // 4. AR AGING
                    $arAgingList = collect(); // Kosongkan sementara jika belum ada tabel Invoice
                    $arTotal = 0;
                    if(class_exists('\App\Models\Invoice')) {
                        $arAgingList = Invoice::with('client')->where('status', '!=', 'Paid')->where('issue_date', '<=', $monthDate)->get();
                        $arTotal = $arAgingList->sum('amount');
                    }
                
                    // Render ke PDF
                    $pdf = Pdf::loadView('pdf.report', [
                        'month' => $data['month'],
                        'year' => $data['year'],
                        'income' => $income,
                        'expense' => $expense,
                        'profit' => $profit,
                        'margin' => $margin,
                        'newClients' => $newClients,
                        'newClientsList' => $newClientsList,
                        'cac' => $cac,
                        'ltv' => $ltv,
                        'pipelineValue' => $pipelineValue,
                        'activeProjects' => $activeProjects,
                        'revPerEmployee' => $revPerEmployee,
                        'runway' => $runway,
                        'arAgingList' => $arAgingList,
                        'arTotal' => $arTotal
                    ]);
                
                    return response()->streamDownload(
                        fn () => print($pdf->output()), 
                        "Rekap_Vodeco_FULL_{$data['year']}_{$data['month']}.pdf"
                    );
                })
        ];
    }
}