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

    public function canAccessPanel(\Filament\Panel $panel): bool
    {
        // 1. Pintu Brankas VIP (Hanya Bos)
        if ($panel->getId() === 'admin') {
            return $this->role === 'admin';
        }

        // 2. Pintu Loket Input
        if ($panel->getId() === 'input') {
            // Bos (admin) bebas masuk ke mana saja untuk sidak
            if ($this->role === 'admin') {
                return true;
            }

            // GEMBOK MUTLAK: Cek apakah akun ini terdaftar di HRD & statusnya 'Active'
            $isOfficialEmployee = $this->employee()->where('status', 'Active')->exists();
            
            if (!$isOfficialEmployee) {
                return false; // Tendang keluar! (Akses Ditolak)
            }

            return in_array($this->role, ['operator', 'hrd', 'karyawan']);
        }

        return false;
    }
    public function employee() {
        return $this->hasOne(Employee::class);
    }

    // RELASI MUTLAK KE PENGAJUAN CUTI (Koreksi kurung tutup nyasar)
    public function leaveRequests() 
    { 
        return $this->hasMany(LeaveRequest::class); 
    }
}