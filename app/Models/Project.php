<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $guarded = []; // Membuka gerbang agar data bisa masuk otomatis

    // PIPA RELASI MUTLAK KE TABEL KLIEN (BARU)
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function employees()
    {
        // Menghubungkan proyek dengan tim beserta radar persentase waktunya
        return $this->belongsToMany(Employee::class)->withPivot('allocation_percentage')->withTimestamps();
    }
}