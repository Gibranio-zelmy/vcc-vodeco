<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;

    // Buka kunci pintu untuk kolom-kolom ini
    protected $fillable = [
        'name',
        'email',
        'phone',
        'address',
        'company_name',
        'join_date',
    ];
}