<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\MassPrunable; // <-- Mantra Pembakar

class LogHistory extends Model
{
    use MassPrunable; // <-- Aktifkan fiturnya di dalam class

    protected $guarded = [];

    // Pipa relasi ke siapa yang melakukan input
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // ATURAN MUTLAK MESIN PEMBAKAR: Hancurkan data yang lebih tua dari 6 bulan
    public function prunable(): Builder
    {
        return static::where('created_at', '<=', now()->subMonths(6));
    }
}