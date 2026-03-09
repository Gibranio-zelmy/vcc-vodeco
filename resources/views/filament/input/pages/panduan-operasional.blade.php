<x-filament-panels::page>
    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
        {{-- Card Fase 1 --}}
        <x-filament::section icon="heroicon-o-user-group" heading="FASE 1: Klien & Pesanan">
            <ul class="list-disc pl-5 space-y-2 text-sm">
                <li><strong>Input Klien Baru:</strong> Daftarkan profil klien yang sudah deal (Setuju)[cite: 3].</li>
                <li><strong>Input Proyek Baru:</strong> Masukkan detail proyek kerjaan klien dan estimasi nilainya.</li>
                <li><strong>Input Aset/Layanan:</strong> Catat seluruh belanja aset klien (Domain, Hosting, Meta Ads) beserta tanggal matinya[cite: 4].</li>
            </ul>
        </x-filament::section>

        {{-- Card Fase 2 --}}
        <x-filament::section icon="heroicon-o-banknotes" heading="FASE 2: Tagihan & Kasir">
            <ul class="list-disc pl-5 space-y-2 text-sm">
                <li><strong>Meja Kasir (Invoice):</strong> Cetak tagihan baru di sini (Otomatis Unpaid)[cite: 6]. Jika ada uang DP/Lunas masuk, wajib klik tombol <b>"Terima Uang"</b> di menu ini[cite: 7].</li>
                <li><strong>Input Pengeluaran:</strong> HANYA untuk mencatat uang keluar (Gaji, Operasional, Iklan).</li>
            </ul>
        </x-filament::section>
    </div>
    
    {{-- Card Peringatan Keras --}}
    <x-filament::section icon="heroicon-o-shield-exclamation" heading="PERINGATAN KERAS (SOP MUTLAK)">
        <p class="text-danger-600 font-bold mb-2">⚠️ HARAM HUKUMNYA MENGINPUT UANG MASUK DARI MENU PENGELUARAN[cite: 7].</p>
        <p class="text-sm">Seluruh Pemasukan (Income) HANYA BISA dicatat dan dihitung otomatis melalui fitur <b>"Terima Uang"</b> di Meja Kasir (Invoice)[cite: 7]. Pelanggaran input akan menyebabkan sistem Radar VIP Perusahaan menjadi berantakan.</p>
    </x-filament::section>
</x-filament-panels::page>