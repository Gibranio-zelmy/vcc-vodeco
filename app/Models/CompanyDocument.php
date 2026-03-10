<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\RecordsActivity;

class CompanyDocument extends Model
{
    protected $guarded = [];
    use RecordsActivity;

    // Mengubah format JSON di database menjadi Array yang dipahami Laravel
    protected $casts = [
        'target_roles' => 'array',
    ];
}