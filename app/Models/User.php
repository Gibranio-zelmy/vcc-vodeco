<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements FilamentUser
{
    use Notifiable;

    protected $guarded = [];

    // ALGORITMA PENJAGA PINTU DUA GEDUNG
    public function canAccessPanel(Panel $panel): bool
    {
        // 1. Pintu Brankas VIP (vodeco.com/admin)
        if ($panel->getId() === 'admin') {
            return $this->role === 'admin'; // HANYA Bos dan C-Level yang bisa tembus
        }

        // 2. Pintu Loket Input (vodeco.com/input)
        if ($panel->getId() === 'input') {
            // Admin boleh masuk untuk nge-tes, Operator wajib masuk ke sini
            return in_array($this->role, ['admin', 'operator']); 
        }

        return false; // Jika ada pintu siluman lain, blokir mutlak
    }
}