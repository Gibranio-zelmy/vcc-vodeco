<?php

namespace App\Filament\Input\Pages;

use Filament\Pages\Page;

class PanduanOperasional extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-book-open';
    protected static string $view = 'filament.input.pages.panduan-operasional';
    protected static ?string $navigationGroup = 'PANDUAN & SOP';
    protected static ?string $navigationLabel = 'Buku Panduan SOP';

    // MEMBUAT JUDUL HALAMAN ADAPTIF SESUAI ROLE KASTA
    public function getTitle(): string
    {
        $role = auth()->user()->role ?? '';

        if ($role === 'karyawan') {
            return 'Panduan Administrasi Karyawan';
        } elseif ($role === 'hrd') {
            return 'SOP Mutlak Departemen HRD';
        }

        return 'SOP Mutlak Data Entry VCC'; // Default untuk Operator & Admin
    }
}