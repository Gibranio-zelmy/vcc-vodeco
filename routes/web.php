<?php

use Illuminate\Support\Facades\Route;

// Kunci pintu depan, paksa semua pengunjung langsung ke Gerbang Terminal (Login)
Route::get('/', function () {
    return redirect('/admin');
});