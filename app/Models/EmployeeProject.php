<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeProject extends Model
{
    // Mengunci radar ke tabel pivot yang benar
    protected $table = 'employee_project';
    
    protected $guarded = [];

    // Relasi balik agar form bisa membaca nama karyawan
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }
}