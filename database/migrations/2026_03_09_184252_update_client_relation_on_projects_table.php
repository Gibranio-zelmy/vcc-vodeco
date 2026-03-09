<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            // 1. Tambahkan pipa relasi ke tabel clients
            $table->foreignId('client_id')->nullable()->constrained('clients')->nullOnDelete()->after('name');
            // 2. Hancurkan laci input manual yang lama
            $table->dropColumn('client_name');
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropForeign(['client_id']);
            $table->dropColumn('client_id');
            $table->string('client_name')->nullable();
        });
    }
};