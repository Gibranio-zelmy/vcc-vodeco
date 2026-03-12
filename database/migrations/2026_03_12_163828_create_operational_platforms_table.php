<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('operational_platforms', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Nama platform (Misal: Niagahoster, Meta Ads)
            $table->string('function')->nullable(); // Fungsi
            $table->string('division')->nullable(); // Divisi yang pegang
            $table->string('url')->nullable(); // Link Platform
            $table->string('username')->nullable(); // Kredensial ID
            $table->string('password')->nullable(); // Kredensial Password
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('operational_platforms');
    }
};