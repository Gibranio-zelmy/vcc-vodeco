<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Paksa cabut aturan kaku dari PostgreSQL
        DB::statement('ALTER TABLE invoices DROP CONSTRAINT IF EXISTS invoices_status_check');
    }

    public function down(): void
    {
        // Kosongkan saja down-nya
    }
};