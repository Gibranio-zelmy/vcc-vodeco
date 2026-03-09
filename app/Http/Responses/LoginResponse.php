<?php

namespace App\Http\Responses;

use Filament\Http\Responses\Auth\Contracts\LoginResponse as LoginResponseContract;
use Illuminate\Http\RedirectResponse;
use Livewire\Features\SupportRedirects\Redirector;

class LoginResponse implements LoginResponseContract
{
    public function toResponse($request): RedirectResponse | Redirector
    {
        $user = auth()->user();

        // ALGORITMA SATPAM CERDAS
        // Jika yang login adalah Bos (admin), buka lift ke VIP
        if ($user->role === 'admin') {
            return redirect()->to('/admin');
        }

        // Jika yang login adalah Kuli/HRD, arahkan ke lantai bawah
        return redirect()->to('/input');
    }
}