<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use \App\Traits\RecordsActivity;

class ReportArchive extends Model
{
    protected $guarded = [];
    use RecordsActivity;
}