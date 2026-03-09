<?php

namespace App\Filament\Input\Pages;

use Filament\Pages\Page;

class PanduanOperasional extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-book-open';
    protected static ?string $navigationLabel = 'Buku Panduan SOP';
    protected static ?string $title = 'SOP Mutlak Data Entry VCC';
    protected static ?int $navigationSort = -1; // Angka minus memaksa menu ini berada di paling atas mutlak
    protected static string $view = 'filament.input.pages.panduan-operasional';
}