<!DOCTYPE html>
<html>
<head>
    <title>Surat Izin Cuti - {{ $record->user->name }}</title>
    <style>
        body { font-family: sans-serif; line-height: 1.6; color: #333; }
        .header { text-align: center; border-bottom: 2px solid #000; padding-bottom: 10px; margin-bottom: 20px; }
        .content { margin: 0 20px; }
        table { width: 100%; margin-top: 10px; }
        td { padding: 5px 0; vertical-align: top; }
        .td-label { width: 30%; font-weight: bold; }
        .footer { margin-top: 50px; text-align: right; border-top: 1px dashed #ccc; padding-top: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <h2>SURAT IZIN CUTI KARYAWAN</h2>
        <p><strong>CV VODECO DIGITAL MEDIATAMA</strong></p>
    </div>
    
    <div class="content">
        <p>Telah disetujui pengajuan cuti untuk karyawan berikut:</p>
        <table>
            <tr><td class="td-label">Nama Karyawan</td><td>: {{ $record->user->name }}</td></tr>
            <tr><td class="td-label">Posisi / Role</td><td>: {{ strtoupper($record->user->role) }}</td></tr>
            <tr><td class="td-label">Tanggal Mulai</td><td>: {{ \Carbon\Carbon::parse($record->start_date)->translatedFormat('d F Y') }}</td></tr>
            <tr><td class="td-label">Tanggal Selesai</td><td>: {{ \Carbon\Carbon::parse($record->end_date)->translatedFormat('d F Y') }}</td></tr>
            <tr><td class="td-label">Alasan Cuti</td><td>: {{ $record->reason }}</td></tr>
        </table>
        <br>
        <p><em>Surat ini merupakan dokumen sah bahwa karyawan tersebut telah mendapatkan izin resmi dari HRD dan Owner.</em></p>
    </div>

    <div class="footer">
        <p>Diterbitkan otomatis oleh <strong>Vodeco Command Center (VCC)</strong></p>
        <p>Status: <strong style="color: green;">APPROVED</strong></p>
    </div>
</body>
</html>