<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('report_archives', function (Blueprint $table) {
            // Tambahkan 3 laci finansial mutlak (format angka besar/decimal)
            $table->decimal('total_revenue', 15, 2)->default(0)->after('year');
            $table->decimal('total_expense', 15, 2)->default(0)->after('total_revenue');
            $table->decimal('net_profit', 15, 2)->default(0)->after('total_expense');
        });
    }

    public function down(): void
    {
        Schema::table('report_archives', function (Blueprint $table) {
            $table->dropColumn(['total_revenue', 'total_expense', 'net_profit']);
        });
    }
};