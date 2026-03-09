<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\RecordsActivity; // Pasang CCTV juga di sini

class LeaveRequest extends Model
{
    use RecordsActivity;

    protected $guarded = [];

    // Relasi ke tabel users
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}