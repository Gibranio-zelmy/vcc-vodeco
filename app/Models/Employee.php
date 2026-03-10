<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use \App\Traits\RecordsActivity;

class Employee extends Model
{
    // KUNCI MASTER: Semua kolom otomatis diizinkan masuk, termasuk 'document_link' yang baru kita buat
    protected $guarded = [];
    use RecordsActivity;

    // Relasi ke tabel Projects untuk menghitung workload/beban kerja
    public function projects()
    {
        return $this->belongsToMany(Project::class)
            ->withPivot('allocation_percentage')
            ->withTimestamps();
    }
    public function user() {
        return $this->belongsTo(User::class);
    }
}