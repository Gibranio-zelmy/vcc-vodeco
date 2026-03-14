<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar; // Mesin Foto Profil
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Jeffgreco13\FilamentBreezy\Traits\TwoFactorAuthenticatable; // Mesin 2FA
use Illuminate\Support\Facades\Storage;
use App\Traits\RecordsActivity; 

// Tambahkan HasAvatar di samping FilamentUser
class User extends Authenticatable implements FilamentUser, HasAvatar
{
    use HasFactory;
    use Notifiable;
    use RecordsActivity;
    use TwoFactorAuthenticatable; // Injeksi DNA 2FA

    protected $guarded = [];

    // ==============================================================
    // GEMBOK LAPIS 2: TOLAK PEMBUATAN/UPDATE AKUN DENGAN EMAIL ASING
    // ==============================================================
    protected static function booted(): void
    {
        static::saving(function ($user) {
            if (!str_ends_with($user->email, '@vodeco.co.id')) {
                throw new \Exception('SABOTASE DICEGAT: Hanya email resmi @vodeco.co.id yang diizinkan masuk ke VCC Terminal!');
            }
        });
    }

    // ==========================================
    // MESIN AVATAR / FOTO PROFIL
    // ==========================================
    public function getFilamentAvatarUrl(): ?string
    {
        return $this->avatar_url ? Storage::url($this->avatar_url) : null;
    }

    // ==========================================
    // GEMBOK KASTA PANEL (VIP vs KULI) + WHITELIST DOMAIN
    // ==========================================
    public function canAccessPanel(\Filament\Panel $panel): bool
    {
        // GEMBOK MUTLAK LAPIS 1: Wajib domain @vodeco.co.id
        // Jika bukan vodeco.co.id, tendang keluar detik ini juga!
        if (!str_ends_with($this->email, '@vodeco.co.id')) {
            return false;
        }

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

            // Cek apakah akun ini terdaftar di HRD & statusnya 'Active'
            $isOfficialEmployee = $this->employee()->where('status', 'Active')->exists();
            
            if (!$isOfficialEmployee) {
                return false; // Tendang keluar! (Akses Ditolak)
            }

            return in_array($this->role, ['operator', 'hrd', 'karyawan']);
        }

        return false;
    }

    // ==========================================
    // RELASI DATABASE - ASLI MILIK BOS
    // ==========================================
    public function employee() {
        return $this->hasOne(Employee::class);
    }

    // RELASI MUTLAK KE PENGAJUAN CUTI
    public function leaveRequests() 
    { 
        return $this->hasMany(LeaveRequest::class); 
    }
}