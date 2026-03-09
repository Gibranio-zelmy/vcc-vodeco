<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use \App\Traits\RecordsActivity;

class Client extends Model
{
    protected $guarded = [];
    use RecordsActivity;

    // Fungsi Gaib: Bekerja otomatis sesaat sebelum data disimpan ke database
    protected static function booted()
    {
        static::creating(function ($client) {
            // 1. Cek tanggal gabung (jika dari Excel pakai join_date, jika manual pakai hari ini)
            $date = $client->join_date ? Carbon::parse($client->join_date) : now();
            
            // 2. Buat Prefix (Contoh: V-2208-)
            $prefix = 'V-' . $date->format('ym') . '-';
            
            // 3. Cari klien terakhir di bulan dan tahun tersebut
            $lastClient = self::where('client_code', 'like', $prefix . '%')
                              ->orderBy('client_code', 'desc')
                              ->first();
                              
            // 4. Hitung urutan selanjutnya
            $lastNumber = $lastClient ? intval(substr($lastClient->client_code, -3)) : 0;
            
            // 5. Cetak Plat Nomor (Contoh: V-2208-001)
            $client->client_code = $prefix . str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
        });
    }

    // Jika Bos nanti punya tabel Invoices / Assets yang relasi ke Client, bisa ditaruh di bawah sini
    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }
}