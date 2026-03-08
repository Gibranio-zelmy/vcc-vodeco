<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Paksa cabut aturan kaku tipe transaksi dari PostgreSQL
        DB::statement('ALTER TABLE transactions DROP CONSTRAINT IF EXISTS transactions_type_check');
    }

    public function down(): void
    {
        // Kosongkan saja
    }
};