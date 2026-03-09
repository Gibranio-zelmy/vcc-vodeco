<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use \App\Traits\RecordsActivity;

class Asset extends Model
{
    use RecordsActivity;
    protected $fillable = [
        'client_id', 'name', 'category', 'platform', 
        'start_date', 'end_date', 'value', 'status', 'credentials_link'
    ];
    
    public function client() {
        return $this->belongsTo(Client::class);
    }
}
