<!DOCTYPE html>
<html>
<head>
    <title>Rekapitulasi Lengkap Vodeco</title>
    <style>
        body { font-family: Helvetica, Arial, sans-serif; color: #222; margin: 30px; font-size: 11px; }
        .header { text-align: center; border-bottom: 2px solid #222; padding-bottom: 10px; margin-bottom: 15px; }
        .title { font-size: 22px; font-weight: bold; letter-spacing: 1px; }
        .subtitle { font-size: 13px; color: #666; margin-top: 5px; }
        
        .grid-table { width: 100%; border-collapse: collapse; margin-bottom: 15px; table-layout: fixed; }
        .grid-table td { width: 50%; padding: 6px; vertical-align: top; border: none; }
        
        .box { border: 1px solid #ddd; padding: 12px; border-radius: 5px; background: #fafafa; }
        .box-highlight { background: #f0fdf4; border-color: #bbf7d0; }
        
        .label { font-size: 10px; color: #777; text-transform: uppercase; font-weight: bold; }
        .value { font-size: 16px; font-weight: bold; margin-top: 4px; color: #111; }
        .sub-text { font-size: 9px; color: #888; margin-top: 3px; }
        
        .text-green { color: #166534; }
        .text-red { color: #991b1b; }
        
        .section-title { font-size: 12px; font-weight: bold; border-bottom: 1px solid #ccc; padding-bottom: 4px; margin-bottom: 10px; margin-top: 10px; background-color: #eee; padding: 5px 10px; }
        
        .data-table { width: 100%; border-collapse: collapse; margin-bottom: 15px; font-size: 10px; }
        .data-table th, .data-table td { border: 1px solid #ddd; padding: 6px; text-align: left; }
        .data-table th { background-color: #f4f4f5; font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">VODECO DIGITAL MEDIATAMA</div>
        <div class="subtitle">Laporan Matriks Komprehensif - Periode {{ $month }} / {{ $year }}</div>
    </div>

    <div class="section-title">1. FINANCIAL PERFORMANCE</div>
    <table class="grid-table">
        <tr>
            <td>
                <div class="box">
                    <div class="label">Pemasukan (Income)</div>
                    <div class="value text-green">Rp {{ number_format($income, 0, ',', '.') }}</div>
                </div>
            </td>
            <td>
                <div class="box">
                    <div class="label">Pengeluaran (Burn Rate)</div>
                    <div class="value text-red">Rp {{ number_format($expense, 0, ',', '.') }}</div>
                </div>
            </td>
        </tr>
        <tr>
            <td>
                <div class="box box-highlight">
                    <div class="label">Net Cashflow (Profit)</div>
                    <div class="value text-green">Rp {{ number_format($profit, 0, ',', '.') }}</div>
                </div>
            </td>
            <td>
                <div class="box">
                    <div class="label">Net Profit Margin</div>
                    <div class="value">{{ $margin }}%</div>
                </div>
            </td>
        </tr>
    </table>

    <div class="section-title">2. OPERATIONAL & PIPELINE</div>
    <table class="grid-table">
        <tr>
            <td>
                <div class="box">
                    <div class="label">Active Projects (Bulan Ini)</div>
                    <div class="value">{{ $activeProjects }} Proyek</div>
                </div>
            </td>
            <td>
                <div class="box">
                    <div class="label">Pipeline Value (Potensi)</div>
                    <div class="value">Rp {{ number_format($pipelineValue, 0, ',', '.') }}</div>
                </div>
            </td>
        </tr>
        <tr>
            <td>
                <div class="box">
                    <div class="label">Company Runway</div>
                    <div class="value">{{ $runway }} Bulan</div>
                    <div class="sub-text">Batas hidup tanpa pemasukan baru</div>
                </div>
            </td>
            <td>
                <div class="box">
                    <div class="label">Rev. per Employee</div>
                    <div class="value">Rp {{ number_format($revPerEmployee, 0, ',', '.') }}</div>
                </div>
            </td>
        </tr>
    </table>

    <div class="section-title">3. MARKETING & CLIENT VALUATION</div>
    <table class="grid-table">
        <tr>
            <td>
                <div class="box">
                    <div class="label">CAC (Biaya Akuisisi)</div>
                    <div class="value">Rp {{ number_format($cac, 0, ',', '.') }}</div>
                    <div class="sub-text">Dari {{ $newClients }} Klien Baru</div>
                </div>
            </td>
            <td>
                <div class="box">
                    <div class="label">Client LTV (Rata-rata)</div>
                    <div class="value text-green">Rp {{ number_format($ltv, 0, ',', '.') }}</div>
                    <div class="sub-text">Valuasi seumur hidup per klien</div>
                </div>
            </td>
        </tr>
    </table>

    <div class="section-title" style="page-break-before: auto;">4. DAFTAR KLIEN BARU (Bulan Ini: {{ $newClients }} Klien)</div>
    @if(count($newClientsList) > 0)
        <table class="data-table">
            <thead>
                <tr>
                    <th>Nama Lengkap</th>
                    <th>Perusahaan</th>
                    <th>WhatsApp</th>
                    <th>Tgl Bergabung</th>
                </tr>
            </thead>
            <tbody>
                @foreach($newClientsList as $client)
                <tr>
                    <td>{{ $client->name }}</td>
                    <td>{{ $client->company_name ?? '-' }}</td>
                    <td>{{ $client->whatsapp ?? '-' }}</td>
                    <td>{{ \Carbon\Carbon::parse($client->join_date)->format('d M Y') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p style="font-size: 10px; color: #666;">Tidak ada penambahan klien baru pada periode ini.</p>
    @endif

    <div class="section-title" style="page-break-before: auto;">5. AR AGING / PIUTANG MACET (Total: Rp {{ number_format($arTotal, 0, ',', '.') }})</div>
    @if(count($arAgingList) > 0)
        <table class="data-table">
            <thead>
                <tr>
                    <th>Klien</th>
                    <th>No. Invoice</th>
                    <th>Tgl Terbit</th>
                    <th>Tgl Jatuh Tempo</th>
                    <th>Status</th>
                    <th>Jumlah (Rp)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($arAgingList as $ar)
                <tr>
                    <td>{{ $ar->client->name ?? 'Unknown' }}</td>
                    <td>{{ $ar->invoice_number }}</td>
                    <td>{{ \Carbon\Carbon::parse($ar->issue_date)->format('d M Y') }}</td>
                    <td class="text-red">{{ \Carbon\Carbon::parse($ar->due_date)->format('d M Y') }}</td>
                    <td><strong>{{ strtoupper($ar->status) }}</strong></td>
                    <td><strong>{{ number_format($ar->amount, 0, ',', '.') }}</strong></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p style="font-size: 10px; color: #166534; font-weight: bold;">Luar biasa! Tidak ada piutang macet. Semua klien sudah melunasi tagihan hingga periode ini.</p>
    @endif

</body>
</html>