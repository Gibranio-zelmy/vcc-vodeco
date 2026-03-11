<x-filament-panels::page>
    @php
        $role = auth()->user()->role ?? '';
    @endphp

    {{-- ======================================================== --}}
    {{-- 1. TAMPILAN KHUSUS OPERATOR (Atau Bos yang lagi sidak)   --}}
    {{-- ======================================================== --}}
    @if(in_array($role, ['operator', 'admin', 'Admin']))
        <div class="p-4 mb-4 text-sm text-blue-800 rounded-lg bg-blue-50 border border-blue-200 shadow-sm">
            <div class="flex items-center gap-3">
                <x-heroicon-o-information-circle class="w-6 h-6 text-blue-600" />
                <span class="font-semibold text-base">PERHATIAN SELURUH OPERATOR DATA ENTRY!</span>
            </div>
            <p class="mt-2 ml-9">Setiap data yang Anda simpan di sistem ini akan <b>langsung memicu Lonceng Radar di ruangan VIP Direksi (Bos) secara real-time</b>. Bekerjalah dengan presisi dan ikuti urutan fase di bawah ini.</p>
        </div>

        <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
            <x-filament::section icon="heroicon-o-user-group" heading="FASE 1: Klien & Aset" class="border-t-4 border-t-primary-500">
                <ul class="list-disc pl-5 space-y-3 text-sm">
                    <li><strong>1. Input Klien Baru:</strong> Segera daftarkan profil perusahaan klien jika ada kesepakatan baru.</li>
                    <li><strong>2. Input Layanan/Aset:</strong> Masukkan detail pesanan (Web, Domain, dll). <b>Tanggal Jatuh Tempo</b> wajib diisi agar Radar H-30 aktif.</li>
                </ul>
            </x-filament::section>

            <x-filament::section icon="heroicon-o-banknotes" heading="FASE 2: Tagihan & Kasir" class="border-t-4 border-t-success-500">
                <ul class="list-disc pl-5 space-y-3 text-sm">
                    <li><strong>1. Cetak Invoice:</strong> Buat tagihan yang disepakati (Otomatis Unpaid).</li>
                    <li><strong>2. Penerimaan Uang:</strong> Jika Klien transfer, klik tombol hijau <b>"Catat Bayar / DP"</b> di tagihan terkait.</li>
                </ul>
            </x-filament::section>

            <x-filament::section icon="heroicon-o-calendar-days" heading="FASE 3: HRD & Cuti" class="border-t-4 border-t-warning-500">
                <ul class="list-disc pl-5 space-y-3 text-sm">
                    <li><strong>Jatah Cuti:</strong> 1 hari/bulan. Dilarang rapel.</li>
                    <li><strong>Sistem Gembok:</strong> Sistem akan menolak jika ada jabatan sama yang sedang libur.</li>
                </ul>
            </x-filament::section>
        </div>
        
        <x-filament::section icon="heroicon-o-shield-exclamation" heading="HUKUM MUTLAK KEAMANAN DATA" class="mt-6 border-2 border-danger-500 bg-danger-50">
            <div class="flex flex-col gap-2">
                <p class="text-danger-700 font-extrabold text-lg uppercase">⚠️ Haram Menginput Uang Masuk Secara Manual!</p>
                <p class="text-sm text-gray-700">Pemasukan (Income) HANYA BISA dicatat otomatis via tombol <b>"Catat Bayar / DP"</b> di tabel Invoice agar kalkulasi piutang VIP tidak bocor.</p>
            </div>
        </x-filament::section>


    {{-- ======================================================== --}}
    {{-- 2. TAMPILAN KHUSUS KARYAWAN (Tim Desain, Produksi, dll)  --}}
    {{-- ======================================================== --}}
    @elseif($role === 'karyawan')
        <div class="p-4 mb-4 text-sm text-emerald-800 rounded-lg bg-emerald-50 border border-emerald-200 shadow-sm">
            <div class="flex items-center gap-3">
                <x-heroicon-o-user class="w-6 h-6 text-emerald-600" />
                <span class="font-semibold text-base">PANDUAN ADMINISTRASI KARYAWAN VODECO</span>
            </div>
            <p class="mt-2 ml-9">Selamat datang di portal VCC. Hak akses Anda difokuskan pada pengurusan administrasi personal (Pengajuan Cuti) dan pengaksesan dokumen legal perusahaan. Silakan patuhi aturan di bawah ini.</p>
        </div>

        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
            <x-filament::section icon="heroicon-o-calendar-days" heading="SOP Pengajuan Cuti" class="border-t-4 border-t-warning-500">
                <ul class="list-disc pl-5 space-y-3 text-sm text-gray-700">
                    <li><strong>Jatah Bulanan:</strong> Cuti operasional diberikan maksimal 1 hari dalam setiap bulannya.</li>
                    <li><strong>Tidak Boleh Dirapel:</strong> Jika cuti bulan ini tidak dipakai, otomatis hangus (tidak diakumulasi ke bulan depan).</li>
                    <li><strong>Radar Anti-Bentrok:</strong> Jika ada rekan satu tim/jabatan yang sudah lebih dulu di-ACC cutinya pada hari tersebut, pengajuan Anda akan ditolak sistem.</li>
                    <li><strong>Alur Proses:</strong> Setelah Anda ajukan, tunggu verifikasi dari HRD. Keputusan Final (ACC/Tolak) berada mutlak di tangan Direksi (Bos).</li>
                </ul>
            </x-filament::section>

            <x-filament::section icon="heroicon-o-document-text" heading="Akses Dokumen Perusahaan" class="border-t-4 border-t-primary-500">
                <ul class="list-disc pl-5 space-y-3 text-sm text-gray-700">
                    <li>Anda dapat melihat <b>SOP Legal, Peraturan Perusahaan, atau Kontrak Kerja</b> di menu <i>Dokumen Perusahaan</i>.</li>
                    <li>Dokumen internal ini dilarang disebarluaskan ke pihak di luar Vodeco.</li>
                </ul>
            </x-filament::section>
        </div>


    {{-- ======================================================== --}}
    {{-- 3. TAMPILAN KHUSUS HRD                                   --}}
    {{-- ======================================================== --}}
    @elseif(in_array($role, ['hrd', 'HRD']))
        <div class="p-4 mb-4 text-sm text-purple-800 rounded-lg bg-purple-50 border border-purple-200 shadow-sm">
            <div class="flex items-center gap-3">
                <x-heroicon-o-briefcase class="w-6 h-6 text-purple-600" />
                <span class="font-semibold text-base">SOP MUTLAK DEPARTEMEN HRD</span>
            </div>
            <p class="mt-2 ml-9">Sebagai HRD, Anda adalah gerbang pertama untuk urusan Manpower dan Legal. Setiap tindakan verifikasi cuti yang Anda lakukan akan langsung meluncur ke Radar VIP Bos.</p>
        </div>

        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
            <x-filament::section icon="heroicon-o-users" heading="Manajemen Database Karyawan" class="border-t-4 border-t-primary-500">
                <ul class="list-disc pl-5 space-y-3 text-sm text-gray-700">
                    <li><strong>Karyawan Baru:</strong> Wajib mendaftarkan data lengkap (Nama, Jabatan, Role). Pastikan menghubungkan akun sistem <i>(User ID)</i> dengan data <i>Employee</i>.</li>
                    <li><strong>Blokir Akses (Resign):</strong> Jika ada karyawan yang *resign*, segera ubah status mereka menjadi <b>"Inactive"</b>. Sistem akan otomatis memblokir akses login mereka ke dalam VCC.</li>
                </ul>
            </x-filament::section>

            <x-filament::section icon="heroicon-o-clipboard-document-check" heading="Verifikasi Berkas Cuti" class="border-t-4 border-t-warning-500">
                <ul class="list-disc pl-5 space-y-3 text-sm text-gray-700">
                    <li><strong>Batas Wewenang:</strong> HRD <b>TIDAK</b> memiliki wewenang untuk Final ACC Cuti. Tugas Anda murni melakukan verifikasi berkas dan memastikan operasional aman.</li>
                    <li><strong>Tindakan:</strong> Jika pengajuan dirasa logis, klik tombol <b>"Verifikasi HRD"</b>. Sistem akan memindahkan bola ke tangan Bos.</li>
                </ul>
            </x-filament::section>
        </div>
    @endif
</x-filament-panels::page>