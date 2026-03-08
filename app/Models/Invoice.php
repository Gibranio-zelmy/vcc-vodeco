<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    // Membuka gembok untuk semua kolom transaksi tagihan
    protected $fillable = [
        'client_id',
        'invoice_number',
        'amount',
        'issue_date',
        'due_date',
        'status',
    ];

    // Relasi agar sistem tahu invoice ini milik client siapa
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function transactions()
    {
        // Relasi invoice dengan transaksi
        return $this->hasMany(Transaction::class);
    }
}