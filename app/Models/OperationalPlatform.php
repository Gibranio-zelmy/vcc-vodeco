<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\RecordsActivity;

class OperationalPlatform extends Model
{
    use RecordsActivity; 

    protected $guarded = [];
}