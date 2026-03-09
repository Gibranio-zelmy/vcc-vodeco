<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    // Jika sudah login, cek jabatannya
    if (auth()->check()) {
        return auth()->user()->role === 'admin' ? redirect('/admin') : redirect('/input');
    }
    // Jika belum login, paksa arahkan ke halaman Login Kuli
    return redirect('/input'); 
});