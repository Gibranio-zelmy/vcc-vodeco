<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE projects DROP CONSTRAINT IF EXISTS projects_status_check');
    }

    public function down(): void
    {
        // Tidak perlu aksi mundur (rollback)
    }
};