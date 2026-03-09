<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    // KUNCI MASTER: Semua kolom otomatis diizinkan masuk, termasuk 'document_link' yang baru kita buat
    protected $guarded = [];

    // Relasi ke tabel Projects untuk menghitung workload/beban kerja
    public function projects()
    {
        return $this->belongsToMany(Project::class)
            ->withPivot('allocation_percentage')
            ->withTimestamps();
    }
}