<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Asset extends Model
{
    protected $fillable = [
        'client_id', 'name', 'category', 'platform', 
        'start_date', 'end_date', 'value', 'status', 'credentials_link'
    ];
    
    public function client() {
        return $this->belongsTo(Client::class);
    }
}
